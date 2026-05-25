<div id="sidebar">
    @php
        $setting = \App\Models\Setting::first();
        $user = auth()->user();

        $isActive = function (array $patterns): bool {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }

            return false;
        };

        $canSee = function (?string $permission, ?string $route = null) use ($user): bool {
            // Super Admin / Admin can see everything
            if ($user?->hasRole('Super Admin') || $user?->hasRole('admin')) {
                return true;
            }

            // Default permission check for staff
            if ($user && !$user->hasRole('Subscriber')) {
                return !$permission || $user->can($permission);
            }

            // Subscriber-specific visibility rules
            // Allowed subscriber modules (route names)
            $subscriberAllowed = [
                'dashboard',
                'dashboard', // analytics maps to dashboard/analytics route
                'subscriber.products.index',
                'subscriber.attributes.index',
                'subscriber.attribute-groups.index',
                'subscriber.share.index',
                'subscriber.inventory.index',
                'subscriber.variants.index',
                'subscriber.profile.edit',
                'subscriber.subscription.index',
                'subscriber.subscription.plans',
                'brands',
                'categories',
                'subcategories',
                'contact',
            ];

            // If no route specified, fall back to permission check
            if (!$route) {
                return !$permission || $user->can($permission);
            }

            // If subscriber has no active subscription, only show basic items
            if (!$user->hasActiveSubscription()) {
                $limited = [
                    'dashboard',
                    'subscriber.profile.edit',
                    'subscriber.subscription.index',
                    'subscriber.subscription.plans',
                ];
                return in_array($route, $limited, true);
            }

            return in_array($route, $subscriberAllowed, true) || (!$permission || $user->can($permission));
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
                ],
            ],
            [
                'label' => 'Content',
                'items' => [
                    [
                        'label' => 'Brands',
                        'icon' => 'bi-tag-fill',
                        'route' => 'brands',
                        'permission' => null,
                        'active' => ['brands', 'brand.*'],
                    ],
                    [
                        'label' => 'Categories',
                        'icon' => 'bi-list-ul',
                        'route' => 'categories',
                        'permission' => null,
                        'active' => ['categories', 'category.*'],
                    ],
                    [
                        'label' => 'Subcategories',
                        'icon' => 'bi-list-columns',
                        'route' => 'subcategories',
                        'permission' => null,
                        'active' => ['subcategories', 'subcategory.*'],
                    ],
                    [
                        'label' => 'Products',
                        'icon' => 'bi-box-seam-fill',
                        'route' => 'subscriber.products.index',
                        'permission' => 'products.view',
                        'active' => ['subscriber.products.*'],
                    ],
                    [
                        'label' => 'Attributes',
                        'icon' => 'bi-sliders',
                        'route' => 'subscriber.attributes.index',
                        'permission' => 'products.view',
                        'active' => ['subscriber.attributes.*'],
                    ],
                    [
                        'label' => 'Variants',
                        'icon' => 'bi-layers',
                        'route' => 'subscriber.variants.index',
                        'permission' => 'products.view',
                        'active' => ['subscriber.variants.*'],
                    ],
                    [
                        'label' => 'Inventory',
                        'icon' => 'bi-archive-fill',
                        'route' => 'subscriber.inventory.index',
                        'permission' => 'products.view',
                        'active' => ['subscriber.inventory.*'],
                    ],
                ],
            ],
            [
                'label' => 'Sharing',
                'items' => [
                    [
                        'label' => 'Share Links',
                        'icon' => 'bi-share-fill',
                        'route' => 'subscriber.share.index',
                        'permission' => 'products.view',
                        'active' => ['subscriber.share.*'],
                    ],
                    [
                        'label' => 'Analytics',
                        'icon' => 'bi-graph-up-arrow',
                        'route' => 'dashboard',
                        'permission' => 'dashboard.analytics',
                        'active' => ['dashboard'],
                    ],
                    [
                        'label' => 'Enquiries',
                        'icon' => 'bi-envelope-fill',
                        'route' => 'contact',
                        'permission' => null,
                        'active' => ['contact'],
                    ],
                    [
                        'label' => 'Newsletters',
                        'icon' => 'bi-newspaper',
                        'route' => 'contact',
                        'permission' => null,
                        'active' => ['contact'],
                    ],
                ],
            ],
            [
                'label' => 'Account',
                'items' => [
                    [
                        'label' => 'Profile',
                        'icon' => 'bi-person-circle',
                        'route' => 'subscriber.profile.edit',
                        'permission' => 'dashboard.view',
                        'active' => ['subscriber.profile.*'],
                    ],
                    [
                        'label' => 'Subscription',
                        'icon' => 'bi-credit-card-2-front-fill',
                        'route' => 'subscriber.subscription.index',
                        'permission' => 'dashboard.view',
                        'active' => ['subscriber.subscription.*'],
                    ],
                    [
                        'label' => 'Support',
                        'icon' => 'bi-life-preserver',
                        'route' => 'contact',
                        'permission' => 'dashboard.view',
                        'active' => ['contact'],
                    ],
                ],
            ],
        ];
    @endphp

    <div class="sidebar-header">
        <div class="sidebar-logo">
            <span class="sidebar-logo-badge">
                <i class="bi bi-cast fs-6"></i>
            </span>
            <span class="sidebar-title-text">
                {{ $setting->site_title ?? 'Catasky' }}
            </span>
        </div>

        <button type="button" class="btn btn-sm btn-outline-light border-0 text-white d-none d-lg-inline-flex align-items-center justify-content-center" onclick="toggleSidebarState()" title="Collapse sidebar" aria-label="Collapse sidebar">
            <i class="bi bi-layout-sidebar-inset"></i>
        </button>
    </div>

    <div class="sidebar-scroll">
        @foreach($menuSections as $section)
            @php
                $visibleItems = collect($section['items'])->filter(fn ($item) => $canSee($item['permission'] ?? null, $item['route'] ?? null));
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

        <form action="{{ route('subscriber.logout') }}" method="POST">
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
