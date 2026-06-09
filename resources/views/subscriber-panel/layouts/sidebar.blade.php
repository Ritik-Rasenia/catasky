<div id="sidebar">
    @php
        $setting = \App\Models\Setting::first();
        $user = auth()->user();
        $sub = $user ? $user->activeSubscription() : null;
        $plan = $sub ? $sub->plan : null;
        $isEnterprise = $plan && $plan->slug === 'enterprise';

        $isActive = function (array $patterns): bool {
            foreach ($patterns as $pattern) {
                if (request()->routeIs($pattern)) {
                    return true;
                }
            }
            return false;
        };

        $canSee = function (?string $permission, ?string $route = null) use ($user, $isEnterprise): bool {
            // Default permission check for staff / non-subscribers
            if ($user && !$user->hasRole('Subscriber')) {
                // Super Admin / Admin can see everything in the workspace
                if ($user->hasRole('Super Admin') || $user->hasRole('admin')) {
                    return true;
                }
                return !$permission || $user->can($permission);
            }

            // Subscriber-specific visibility rules
            $subscriberAllowed = [
                'dashboard',
                'subscriber.analytics',
                'subscriber.products.index',
                'subscriber.attributes.index',
                'subscriber.attribute-groups.index',
                'subscriber.share.index',
                'subscriber.inventory.index',
                'subscriber.variants.index',
                'subscriber.profile.edit',
                'subscriber.subscription.index',
                'subscriber.subscription.plans',
                'subscriber.brands.index',
                'subscriber.categories.index',
                'subscriber.subcategories.index',
                'subscriber.domain.index',
                'subscriber.notifications.index',
                'contact',
            ];

            if (!$route) {
                return !$permission || ($user && $user->can($permission));
            }

            // Hide Custom Domain if not enterprise
            if ($route === 'subscriber.domain.index' && !$isEnterprise) {
                return false;
            }

            // If subscriber is pending approval or has no active subscription, strictly limit visible tabs to basic items
            $subProfile = $user ? $user->subscriberProfile : null;
            $isApproved = $subProfile && $subProfile->isApproved();
            if (!$isApproved || !$user || !$user->hasActiveSubscription()) {
                $limited = [
                    'dashboard',
                    'subscriber.profile.edit',
                    'subscriber.subscription.index',
                    'subscriber.subscription.plans',
                ];
                return in_array($route, $limited, true);
            }

            return in_array($route, $subscriberAllowed, true) || (!$permission || ($user && $user->can($permission)));
        };

        $menuSections = [
            [
                'label' => 'Dashboard',
                'items' => [
                    [
                        'label'      => 'Dashboard',
                        'icon'       => 'bi-grid-1x2-fill',
                        'route'      => 'dashboard',
                        'permission' => 'dashboard.view',
                        'active'     => ['dashboard'],
                    ],
                    [
                        'label'      => 'Analytics',
                        'icon'       => 'bi-graph-up-arrow',
                        'route'      => 'subscriber.analytics',
                        'permission' => null,
                        'active'     => ['subscriber.analytics', 'subscriber.analytics.*'],
                    ],
                ],
            ],
            [
                'label' => 'Catalogue',
                'items' => [
                   
                    [
                        'label'      => 'Brands',
                        'icon'       => 'bi-patch-check-fill',
                        'route'      => 'subscriber.brands.index',
                        'permission' => null,
                        'active'     => ['subscriber.brands.*'],
                    ],
                     
                    [
                        'label'      => 'Categories',
                        'icon'       => 'bi-layers-fill',
                        'route'      => 'subscriber.categories.index',
                        'permission' => null,
                        'active'     => ['subscriber.categories.*'],
                    ],
                    [
                        'label'      => 'Subcategories',
                        'icon'       => 'bi-list-nested',
                        'route'      => 'subscriber.subcategories.index',
                        'permission' => null,
                        'active'     => ['subscriber.subcategories.*'],
                    ],
                    [
                        'label'      => 'Products',
                        'icon'       => 'bi-box-seam-fill',
                        'route'      => 'subscriber.products.index',
                        'permission' => 'products.view',
                        'active'     => ['subscriber.products.*'],
                    ],
                ],
            ],
            [
                'label' => 'Engagement',
                'items' => [
                    [
                        'label'      => 'Notifications',
                        'icon'       => 'bi-bell-fill',
                        'route'      => 'subscriber.notifications.index',
                        'permission' => null,
                        'active'     => ['subscriber.notifications.*'],
                    ],
                ],
            ],
            [
                'label' => 'Account',
                'items' => [
                    [
                        'label'      => 'Store Settings',
                        'icon'       => 'bi-gear-fill',
                        'route'      => 'subscriber.profile.edit',
                        'permission' => 'dashboard.view',
                        'active'     => ['subscriber.profile.*'],
                    ],
                    [
                        'label'      => 'Custom Domain',
                        'icon'       => 'bi-globe2',
                        'route'      => 'subscriber.domain.index',
                        'permission' => null,
                        'active'     => ['subscriber.domain.*'],
                    ],
                    [
                        'label'      => 'Subscription',
                        'icon'       => 'bi-credit-card-2-front-fill',
                        'route'      => 'subscriber.subscription.index',
                        'permission' => 'dashboard.view',
                        'active'     => ['subscriber.subscription.*'],
                    ],
                ],
            ],
        ];
    @endphp

    <div class="sidebar-header">
        <div class="sidebar-logo">
            @php
                $profile = $user ? $user->subscriberProfile : null;
            @endphp
            @if($profile && $profile->logo)
                <img src="{{ $profile->logo_url }}" alt="{{ $profile->company_name }}" class="sidebar-logo-img" style="max-height:40px;width:auto;margin-right:8px;border-radius:6px;" />
            @elseif($setting && $setting->logo)
                <img src="{{ asset('uploads/settings/' . $setting->logo) }}" alt="{{ $setting->site_title ?? 'Catasky' }}" class="sidebar-logo-img" style="width:150px;margin:auto;border-radius:6px;" />
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
                $visibleItems = collect($section['items'])->filter(function ($item) use ($canSee) {
                    return $canSee($item['permission'] ?? null, $item['route'] ?? null);
                });
                $sectionSlug = \Illuminate\Support\Str::slug($section['label']);
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
                            $href = isset($item['route']) ? route($item['route'], $item['route_params'] ?? []) : ($item['url'] ?? '#');
                            
                            $active = false;
                            if (isset($item['route']) && $item['route'] === 'subscriber.profile.edit') {
                                $active = request()->routeIs('subscriber.profile.edit');
                            } else {
                                $active = $isActive($item['active'] ?? []);
                            }
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
