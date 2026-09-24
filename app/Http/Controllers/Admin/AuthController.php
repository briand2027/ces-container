<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;use App\Models\Admin;use Illuminate\Http\Request;use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
 public function login(){return view('admin.auth.login');}
 public function authenticate(Request $request){$data=$request->validate(['email'=>'required|email','password'=>'required|string']);$admin=Admin::where('email',$data['email'])->where('statut','actif')->first();if(!$admin||!Hash::check($data['password'],$admin->mot_de_passe))return back()->withErrors(['email'=>'Identifiants incorrects.'])->onlyInput('email');session(['admin_id'=>$admin->id,'admin_nom'=>$admin->nom,'admin_prenom'=>$admin->prenom,'admin_role'=>$admin->role,'admin_email'=>$admin->email]);$request->session()->regenerate();return redirect()->route('admin.dashboard');}
 public function logout(Request $request){$request->session()->invalidate();$request->session()->regenerateToken();return redirect()->route('admin.login');}
}
