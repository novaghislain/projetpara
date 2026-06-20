<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Product;
use App\Models\PosSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommerceDashboardController extends Controller
{
    public function index()
    {
        return view('app', ['page' => 'commerce-dashboard']);
    }

    public function stats()
    {
        $clientId = Auth::user()->client_id ?? Auth::id();

        // Chiffre d'affaires
        $today = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('total_ttc');

        $month = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_ttc');

        $year = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->sum('total_ttc');

        // Transactions
        $todayTransactions = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->count();

        $monthTransactions = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Ticket moyen
        $avgTicket = $monthTransactions > 0 ? $month / $monthTransactions : 0;
        $avgTicketToday = $todayTransactions > 0 ? $today / $todayTransactions : 0;

        // Top produits
        $topProducts = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.client_id', $clientId)
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->select(
                'products.id',
                'products.name',
                \DB::raw('SUM(sale_items.quantity) as total_qty'),
                \DB::raw('SUM(sale_items.total_ttc) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();

        // Ventes par catégorie
        $salesByCategory = \DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->where('sales.client_id', $clientId)
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->select(
                'product_categories.name',
                \DB::raw('SUM(sale_items.total_ttc) as total')
            )
            ->groupBy('product_categories.name')
            ->orderByDesc('total')
            ->get();

        // Ventes par mode de paiement
        $salesByPayment = \DB::table('payments')
            ->join('sales', 'payments.sale_id', '=', 'sales.id')
            ->where('sales.client_id', $clientId)
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->select('payments.payment_method', \DB::raw('SUM(payments.amount) as total'))
            ->groupBy('payments.payment_method')
            ->get();

        // Ventes par caissier
        $salesByCashier = \DB::table('sales')
            ->join('business_users', 'sales.business_user_id', '=', 'business_users.id')
            ->join('users', 'business_users.user_id', '=', 'users.id')
            ->where('sales.client_id', $clientId)
            ->where('sales.status', 'completed')
            ->whereMonth('sales.created_at', now()->month)
            ->whereYear('sales.created_at', now()->year)
            ->select('users.name', \DB::raw('COUNT(*) as total_sales'), \DB::raw('SUM(sales.total_ttc) as total_amount'))
            ->groupBy('users.name')
            ->get();

        // Évolution journalière (30 jours)
        $dailyRevenue = Sale::where('client_id', $clientId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', now()->subDays(30))
            ->select(
                \DB::raw('DATE(created_at) as date'),
                \DB::raw('SUM(total_ttc) as total'),
                \DB::raw('COUNT(*) as count')
            )
            ->groupBy(\DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Alertes stock
        $stockAlerts = Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereColumn('stock_qty', '<=', 'stock_alert');
            })
            ->count();

        $stockCritical = Product::where('client_id', $clientId)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->where('stock_qty', '<=', 0);
            })
            ->count();

        // Produits
        $totalProducts = Product::where('client_id', $clientId)->count();

        return response()->json([
            'revenue' => [
                'today' => (float) $today,
                'month' => (float) $month,
                'year' => (float) $year,
            ],
            'transactions' => [
                'today' => $todayTransactions,
                'month' => $monthTransactions,
            ],
            'avg_ticket' => (float) $avgTicket,
            'avg_ticket_today' => (float) $avgTicketToday,
            'top_products' => $topProducts,
            'sales_by_category' => $salesByCategory,
            'sales_by_payment' => $salesByPayment,
            'sales_by_cashier' => $salesByCashier,
            'daily_revenue' => $dailyRevenue,
            'stock_alerts' => $stockAlerts,
            'stock_critical' => $stockCritical,
            'total_products' => $totalProducts,
        ]);
    }
}
