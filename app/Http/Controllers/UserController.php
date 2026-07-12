<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{

    public function index(){
        $user = User::with(['role'])->orderBy('id','desc')->get();
        return response()->json([
            'users' => $user
        ]);
    }

    public function show($id){
        $user = User::query()->whereKey($id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json([
            'user' => $user
        ]);
    }

    public function store(Request $request){
        $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role_id' => 'required',
        ]);

        // Génération automatique du mot de passe
        $password = Str::random(10);

        $user = User::create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => bcrypt($password),
        ]);

        return response()->json([
            'status' => 200,
            'user' => $user
        ]);

    }

    public function update(Request $request, $id){
        // Validate incoming data (only the fields we allow)
        $rules = [
            'full_name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'role_id' => 'sometimes|',
            'password' => 'sometimes|string|min:8|confirmed',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update allowed fields
        if ($request->has('full_name')) $user->full_name = $request->full_name;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('role_id')) $user->role_id = $request->role_id;

        if ($request->filled('password')) {
            // If password provided, ensure confirmation handled by validation rule 'confirmed'
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return response()->json([
            'status' => 200,
            'user' => $user
        ]);
    }

    public function destroy($id){
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Empêche un admin de se supprimer lui-même par erreur
        if (Auth::id() == $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas supprimer votre propre compte'], 422);
        }

        $user->delete();

        return response()->json([
            'message' => 'Utilisateur supprimé avec succès'
        ], 200);
    }

    public function login(Request $request){
        $request->validate([
            "email"=>"required",
            "password"=>"required",
        ]);



        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
           return response()->json([
            "message"=>"Identifiants incorrects"
           ],401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;



        return response()->json([
            'message'=>'Connexion réussie',
            'user'=>$user,
            'token' => $token
        ]);
    }

    public function getUser(Request $request){
        $user = User::with('role')->find(Auth::id());
        return response()->json([
            "user" => $user,
        ]);
    }

    public function logout(Request $request){
        $user = Auth::user();
        if ($user) {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                "message"=>"Deconnexion"
            ],200);
        }
    }

}
