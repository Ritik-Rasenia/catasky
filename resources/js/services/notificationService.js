import axios from 'axios';
import alertService from './alertService';

const priorityMap = {
    critical: { class: 'bg-danger', text: 'Critical', rank: 4 },
    high: { class: 'bg-warning text-dark', text: 'High', rank: 3 },
    medium: { class: 'bg-primary', text: 'Medium', rank: 2 },
    low: { class: 'bg-info text-dark', text: 'Low', rank: 1 },
};

const getBaseUrl = () => {
    let base = window.baseUrl || '';
    if (base.endsWith('/')) {
        base = base.slice(0, -1);
    }
    return base;
};

const endpoint = (path) => `${getBaseUrl()}/api/notifications${path}`;

const notificationService = {
    listeners: new Set(),
    items: [],
    unreadCount: 0,

    init({ pollInterval = 60000 } = {}) {
        const isAuthenticated = document.querySelector('meta[name="authenticated"]')?.content === 'true' || !!document.querySelector('meta[name="user-id"]')?.content;
        if (!isAuthenticated) {
            return;
        }
        this.refresh();
        this.bindRealtime();
        if (pollInterval > 0) {
            window.setInterval(() => this.refresh({ silent: true }), pollInterval);
        }
    },

    subscribe(listener) {
        this.listeners.add(listener);
        listener(this.snapshot());
        return () => this.listeners.delete(listener);
    },

    emit() {
        const snapshot = this.snapshot();
        this.listeners.forEach((listener) => listener(snapshot));
        window.dispatchEvent(new CustomEvent('catasky:notifications', { detail: snapshot }));
    },

    snapshot() {
        return {
            items: [...this.items],
            unreadCount: this.unreadCount,
        };
    },

    async refresh({ silent = false } = {}) {
        try {
            const response = await axios.get(endpoint(''));
            const payload = response.data || {};
            this.items = Array.isArray(payload) ? payload : (payload.data || payload.notifications || []);
            this.unreadCount = payload.unread_count ?? this.items.filter((item) => !item.read_at && !item.is_read).length;
            this.emit();
            return this.snapshot();
        } catch (error) {
            if (!silent) {
                alertService.toastError('Unable to load notifications.');
            }
            return this.snapshot();
        }
    },

    async markAsRead(notificationId) {
        try {
            await axios.post(endpoint(`/${notificationId}/read`));
            this.items = this.items.map((item) => (
                String(item.id) === String(notificationId)
                    ? { ...item, is_read: true, read_at: item.read_at || new Date().toISOString() }
                    : item
            ));
            this.unreadCount = Math.max(0, this.unreadCount - 1);
            this.emit();
            return true;
        } catch (error) {
            alertService.toastError('Could not mark notification as read.');
            return false;
        }
    },

    async markAllAsRead() {
        try {
            await axios.post(endpoint('/mark-all-read'));
            this.items = this.items.map((item) => ({
                ...item,
                is_read: true,
                read_at: item.read_at || new Date().toISOString(),
            }));
            this.unreadCount = 0;
            this.emit();
            return true;
        } catch (error) {
            alertService.toastError('Could not mark notifications as read.');
            return false;
        }
    },

    async getUnreadCount() {
        try {
            const response = await axios.get(endpoint('/unread-count'));
            this.unreadCount = response.data.count || 0;
            this.emit();
            return this.unreadCount;
        } catch (error) {
            return this.unreadCount;
        }
    },

    push(notification) {
        this.items = [notification, ...this.items];
        if (!notification.read_at && !notification.is_read) {
            this.unreadCount += 1;
        }
        this.emit();
        this.present(notification);
    },

    present(notification) {
        const message = notification.message || notification.title || 'New notification';
        const priority = String(notification.priority || 'low').toLowerCase();
        if (priority === 'critical') {
            alertService.errorAlert(notification.title || 'Critical Alert', message);
            return;
        }
        if (priority === 'high') {
            alertService.toastWarning(message);
            return;
        }
        alertService.toastInfo(message);
    },

    notifySuccess(message, title = 'Success') {
        alertService.notifySuccess(message || title);
    },

    notifyError(message, title = 'Error') {
        alertService.notifyError(message || title);
    },

    bindRealtime() {
        if (window.Echo && !this.echoBound) {
            const userId = document.querySelector('meta[name="user-id"]')?.content;
            const channel = userId ? `users.${userId}.notifications` : 'notifications';
            window.Echo.private(channel).notification((notification) => this.push(notification));
            this.echoBound = true;
        }

        window.addEventListener('catasky:notification', (event) => {
            if (event.detail) {
                this.push(event.detail);
            }
        });
    },

    getPriorityLabel(priority = 'low') {
        return priorityMap[String(priority).toLowerCase()] || { class: 'bg-secondary', text: priority, rank: 0 };
    },
};

export default notificationService;
