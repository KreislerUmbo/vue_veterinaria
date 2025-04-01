<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $users = User::where(DB::raw("users.name || ' ' || COALESCE(users.surname,'') || ' ' || users.n_document"), "ilike", "%" . $search . "%")->orderBy("id", "desc")->get();
        return response()->json([
            "users" => UserCollection::make($users),
            "roles" => Role::where("name", "not ilike", "%veterinario%")->get()->map(function ($role) {
                return [
                    "id" => $role->id,
                    "name" => $role->name,
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $is_user_exists = User::where("email", $request->email)->first();
        if ($is_user_exists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El usuario ya Existe",
            ]);
        }
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("users", $request->file("imagen"));
            // $request->$request->add(["avatar" => $path]);
            $request->merge(["avatar" => $path]);
        }
        if ($request->password) {
            $request->merge(["password" => bcrypt($request->password)]);
        }
        if ($request->birthday) {
            $request->merge(['birthday' => Carbon::parse($request->birthday)]);
        }


        $user =  User::create($request->all());
        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);
        return response()->json([
            "message" => 200,
            "user" => UserResource::make($user),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $is_user_exists = User::where("email", $request->email)->where("id", "<>", $id)->first();
        if ($is_user_exists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El usuario ya Existe",
            ]);
        }

        $user =  User::findOrFail($id);
        if ($request->hasFile("imagen")) {

            // Si ya existe una imagen de avatar, eliminarla del almacenamiento
            if ($user->avatar && Storage::exists($user->avatar)) {
                Storage::delete($user->avatar);
            }
            // Subir la nueva imagen
            $path = Storage::putFile("users", $request->file('imagen'));
            // Agregar la ruta de la imagen al request
            $request->merge(["avatar" => $path]);
        }
        // Si el usuario cambia la contraseña
        if ($request->password) {
            $request->merge(["password" => bcrypt($request->password)]);
        }

        $user->update($request->all());
        // Manejo de roles si se cambia el rol
        if ($request->role_id && $request->role_id != $user->role_id) {
            $role_old = Role::findOrFail($request->role_id);
            $user->removeRole($role_old);

            $role_new = Role::findOrFail($request->role_id);
            $user->assignRole($role_new);
        }

        return response()->json([
            "message" => 200,
            "user" => UserResource::make($user),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if ($user->avatar && Storage::exists($user->avatar)) {
            Storage::delete($user->avatar);
        }

        $user->delete();
        return response()->json(
            [
                "message" => 200,
            ]
        );
    }
}
