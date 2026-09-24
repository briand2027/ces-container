<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\Container;use App\Models\Order;use App\Models\ContactMessage;
class DashboardController extends Controller{public function index(){return view('admin.dashboard.index',['orders'=>Order::latest()->limit(8)->get(),'stats'=>['products'=>Container::count(),'orders'=>Order::count(),'messages'=>ContactMessage::where('statut','non_lu')->count(),'pending'=>Order::where('statut_paiement','en_attente')->count()]]);}}
