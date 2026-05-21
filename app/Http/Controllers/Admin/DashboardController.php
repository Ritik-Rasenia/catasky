<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\User;
use App\Models\Enquiry;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('view-dashboard')) {
            return view('admin.dashboard.limited');
        }

        $user = auth()->user();
        
        $data = [
            'brandsCount' => Brand::count(),
            'categoriesCount' => Category::count(),
            'subcategoriesCount' => Subcategory::count(),
            'productsCount' => Product::count(),
            'usersCount' => User::count(),
            'rolesCount' => Role::count(),
            'enquiriesCount' => Enquiry::count(),
            'recentProducts' => Product::with('category')->latest()->take(5)->get(),
            'recentUsers' => User::latest()->take(5)->get(),
            'recentEnquiries' => Enquiry::latest()->take(5)->get(),
            
            // Enhanced Analytics Data
            'analytics' => [
                'visits' => [1200, 1500, 1100, 1800, 2200, 2500, 2100],
                'shares' => [45, 52, 38, 65, 82, 95, 88],
                'enquiries' => [12, 15, 10, 18, 22, 25, 21],
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                'sharing_breakdown' => [
                    'whatsapp' => 1450,
                    'pdf' => 820,
                    'link' => 1221
                ]
            ]
        ];

        if ($user->hasRole('Admin')) {
            return view('admin.dashboard.admin', $data);
        } elseif ($user->hasRole('Staff')) {
            return view('admin.dashboard.staff', $data);
        }

        return view('admin.dashboard.index', $data);
    }

}
