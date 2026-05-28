<div id="sidebar">
    @php
        $setting = \App\Models\Setting::first();
        $user = auth()->user();
        $unreadEnquiriesCount = \App\Models\Enquiry::where('is_read', false)->count();
            // Pending approvals across SaaS (accounts, store configs) and attribute approvals
            $pendingAccountsCount = \App\Models\SubscriberProfile::where('status', 'pending')->count();
            $pendingStoreConfigsCount = \App\Models\SubscriberProfile::where('status', 'approved')->where('store_status', 'pending')->count();
            $pendingAttributeApprovals = \App\Models\Attribute::where('approval_status', 'pending')->count();
            $pendingApprovalsCount = $pendingAccountsCount + $pendingStoreConfigsCount + $pendingAttributeApprovals;

        $isActive = function (array $patterns): bool {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }

            return false;
        };

        $canSee = function (?string $permission) use ($user): bool {
            return ! $permission || $user?->can($permission);
        };

        $menuSections = [
            [
                'label' => 'Dashboard',
                'items' => [
                    [
                        'label' => 'Dashboard',
                        'icon' => 'bi-grid-1x2-fill',
                        'route' => 'dashboard',
                        'permission' => 'dashboard.view',
                        'active' => ['dashboard'],
                    ],
                    [
                        'label' => 'Analytics',
                        'icon' => 'bi-graph-up-arrow',
                        'route' => 'admin.tracking.analytics',
                        'permission' => 'dashboard.analytics',
                        'active' => ['admin.tracking.analytics'],
                    ],
                ],
            ],
            [
                'label' => 'Catalogue',
                'items' => [
                    [
                        'label' => 'Brands',
                        'icon' => 'bi-patch-check-fill',
                        'route' => 'admin.brands.index',
                        'permission' => 'brands.view',
                        'active' => ['admin.brands.*'],
                    ],
                    [
                        'label' => 'Categories',
                        'icon' => 'bi-layers-fill',
                        'route' => 'admin.categories.index',
                        'permission' => 'categories.view',
                        'active' => ['admin.categories.*'],
                    ],
                    [
                        'label' => 'Subcategories',
                        'icon' => 'bi-list-nested',
                        'route' => 'admin.subcategories.index',
                        'permission' => 'categories.view',
                        'active' => ['admin.subcategories.*'],
                    ],
                    [
                        'label' => 'Products',
                        'icon' => 'bi-box-seam-fill',
                        'route' => 'admin.products.index',
                        'permission' => 'products.view',
                        'active' => ['admin.products.*'],
                    ],
                    [
                        'label' => 'Attributes',
                        'icon' => 'bi-sliders',
                        'route' => 'admin.attributes.index',
                        'permission' => 'products.view',
                        'active' => ['admin.attributes.*'],
                    ],
                ],
            ],
            [
                'label' => 'Engagement',
                'items' => [
                    [
                        'label' => 'Enquiries',
                        'icon' => 'bi-chat-left-dots-fill',
                        'route' => 'admin.enquiries.index',
                        'permission' => 'enquiries.view',
                        'active' => ['admin.enquiries.*'],
                        'badge' => $unreadEnquiriesCount > 0 ? $unreadEnquiriesCount : null,
                    ],
                    [
                        'label' => 'Notifications',
                        'icon' => 'bi-bell-fill',
                        'route' => 'admin.notifications.index',
                        'permission' => 'system.manage',
                        'active' => ['admin.notifications.*'],
                        'badge' => $pendingApprovalsCount > 0 ? $pendingApprovalsCount : null,
                    ],
                    [
                        'label' => 'Newsletters',
                        'icon' => 'bi-envelope-paper-fill',
                        'route' => 'admin.newsletters.index',
                        'permission' => 'newsletters.view',
                        'active' => ['admin.newsletters.*'],
                    ],
                ],
            ],
            [
                'label' => 'SaaS Management',
                'items' => [
                    [
                        'label' => 'Subscribers',
                        'icon' => 'bi-people-fill',
                        'route' => 'admin.subscribers.index',
                        'permission' => 'subscribers.manage',
                        'active' => ['admin.subscribers.*'],
                    ],
                    [
                        'label' => 'Subscription Plans',
                        'icon' => 'bi-credit-card-2-front-fill',
                        'route' => 'admin.subscription-plans.index',
                        'permission' => 'subscribers.manage',
                        'active' => ['admin.subscription-plans.*'],
                    ],
                    [
                        'label' => 'Pending Approvals',
                        'icon' => 'bi-check2-square',
                        'route' => 'admin.saas.approvals.index',
                        'permission' => 'system.manage',
                        'active' => ['admin.saas.approvals.*'],
                            'badge' => $pendingApprovalsCount > 0 ? $pendingApprovalsCount : null,
                    ],
                    [
                        'label' => 'Payments',
                        'icon' => 'bi-cash-stack',
                        'route' => 'admin.saas.payments.index',
                        'permission' => 'system.manage',
                        'active' => ['admin.saas.payments.*'],
                    ],
                    [
                        'label' => 'Invoices',
                        'icon' => 'bi-receipt',
                        'route' => 'admin.saas.invoices.index',
                        'permission' => 'system.manage',
                        'active' => ['admin.saas.invoices.*'],
                    ],
                ],
            ],
            [
                'label' => 'Access Control',
                'items' => [
                    [
                        'label' => 'Users',
                        'icon' => 'bi-people-fill',
                        'route' => 'admin.users.index',
                        'permission' => 'users.view',
                        'active' => ['admin.users.*'],
                    ],
                    [
                        'label' => 'Roles',
                        'icon' => 'bi-shield-lock-fill',
                        'route' => 'admin.roles.index',
                        'permission' => 'roles.manage',
                        'active' => ['admin.roles.*'],
                    ],
                    [
                        'label' => 'Permissions',
                        'icon' => 'bi-key-fill',
                        'route' => 'admin.permissions.index',
                        'permission' => 'permissions.manage',
                        'active' => ['admin.permissions.*'],
                    ],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    [
                        'label' => 'Settings',
                        'icon' => 'bi-gear-fill',
                        'route' => 'admin.settings.index',
                        'permission' => 'settings.manage',
                        'active' => ['admin.settings.*'],
                    ],
                    [
                        'label' => 'System Tools',
                        'icon' => 'bi-shield-fill-exclamation',
                        'route' => 'admin.system.index',
                        'permission' => 'system.manage',
                        'active' => ['admin.system.*'],
                    ],
                    [
                        'label' => 'Profile',
                        'icon' => 'bi-person-circle',
                        'route' => 'admin.profile.edit',
                        'permission' => 'dashboard.view',
                        'active' => ['admin.profile.*'],
                    ],
                ],
            ],
        ];
    @endphp

    <div class="sidebar-header">
        <div class="sidebar-logo">
            @if($setting->logo)
                <img src="{{ asset('uploads/settings/' . $setting->logo) }}" alt="{{ $setting->site_title ?? 'Catasky' }}" class="sidebar-logo-img" style="width:150px;margin:auto;" />
            @else
                <span class="sidebar-logo-badge">
                    <i class="bi bi-cast fs-6"></i>
                </span>
            @endif
        </div>
    </div>

    <div class="sidebar-scroll">
        @foreach($menuSections as $section)
            @php
                $visibleItems = collect($section['items'])->filter(fn ($item) => $canSee($item['permission'] ?? null));
                $sectionSlug = Str::slug($section['label']);
            @endphp

            @if($visibleItems->isNotEmpty())
                <div class="section-title d-flex justify-content-between align-items-center" 
                     data-bs-toggle="collapse" 
                     data-bs-target="#collapse-{{ $sectionSlug }}" 
                     aria-expanded="true" 
                     style="cursor: pointer; user-select: none;">
                    <span class="sidebar-title-text">{{ $section['label'] }}</span>
                    <i class="bi bi-chevron-down section-caret text-muted smaller d-none d-lg-inline" style="transition: transform 0.2s ease; font-size: 0.7rem;"></i>
                </div>

                <div class="collapse show section-collapse-wrapper" id="collapse-{{ $sectionSlug }}">
                    @foreach($visibleItems as $item)
                        @php
                            $active = $isActive($item['active'] ?? []);
                            $href = isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#');
                        @endphp

                        <a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }}">
                            <i class="bi {{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>

                            @if(!empty($item['badge']))
                                <span class="badge rounded-pill ms-auto" style="background:#ef4444;color:#fff;font-size:0.62rem;padding:2px 7px;font-weight:800;">
                                    {{ $item['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        @endforeach
    </div>

    <div class="p-3 border-top border-light border-opacity-10">
        <a href="{{ route('catalogue') }}" target="_blank" class="nav-link mb-2">
            <i class="bi bi-box-arrow-up-right"></i>
            <span class="sidebar-footer-text">View Catalogue</span>
        </a>

        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn w-100 d-flex align-items-center justify-content-center gap-2" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.12);border-radius:12px;color:#fc8181;font-size:0.82rem;font-weight:700;padding:10px 16px;">
                <i class="bi bi-box-arrow-right"></i>
                <span class="sidebar-footer-text">Sign Out</span>
            </button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sections = document.querySelectorAll('.section-collapse-wrapper');
        
        sections.forEach(function (section) {
            const slug = section.getAttribute('id');
            const collapsedKey = 'catasky-section-collapsed-' + slug;
            const isCollapsed = localStorage.getItem(collapsedKey) === 'true';
            
            // Auto expand if contains active nav item
            const hasActive = section.querySelector('.nav-link.active') !== null;
            
            if (isCollapsed && !hasActive) {
                const bsCollapse = new bootstrap.Collapse(section, { toggle: false });
                bsCollapse.hide();
                const title = document.querySelector('[data-bs-target="#' + slug + '"]');
                if (title) {
                    title.setAttribute('aria-expanded', 'false');
                    const caret = title.querySelector('.section-caret');
                    if (caret) caret.style.transform = 'rotate(-90deg)';
                }
            }
        });

        // Event listeners to toggle caret rotation and store state in localStorage
        sections.forEach(function (section) {
            const slug = section.getAttribute('id');
            const collapsedKey = 'catasky-section-collapsed-' + slug;

            section.addEventListener('show.bs.collapse', function () {
                localStorage.setItem(collapsedKey, 'false');
                const title = document.querySelector('[data-bs-target="#' + slug + '"]');
                if (title) {
                    const caret = title.querySelector('.section-caret');
                    if (caret) caret.style.transform = 'rotate(0deg)';
                }
            });

            section.addEventListener('hide.bs.collapse', function () {
                localStorage.setItem(collapsedKey, 'true');
                const title = document.querySelector('[data-bs-target="#' + slug + '"]');
                if (title) {
                    const caret = title.querySelector('.section-caret');
                    if (caret) caret.style.transform = 'rotate(-90deg)';
                }
            });
        });
    });
</script>
