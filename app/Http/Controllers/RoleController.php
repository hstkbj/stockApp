<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{

    /**
     * Liste des pages disponibles (utilisée pour construire le formulaire de droits côté front)
     */
    public function pages()
    {
        return response()->json(config('app_pages', []));
    }

    public function index()
    {
        $roles = Role::with('permissions')->orderBy('name')->get();
        return response()->json($roles);
    }

    public function show($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        return response()->json($role);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
        ]);

        $role = DB::transaction(function () use ($validated) {
            $role = Role::create(['name' => $validated['name']]);

            // On initialise une ligne de permissions à false pour chaque page connue
            foreach (config('app_pages', []) as $routeName => $label) {
                Permission::create([
                    'role_id'     => $role->id,
                    'route_name'  => $routeName,
                    'create'      => false,
                    'read'        => false,
                    'update'      => false,
                    'delete'      => false,
                    'access_page' => false,
                ]);
            }

            return $role;
        });

        $role->load('permissions');
        return response()->json($role, 200);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
        ]);

        $role->update($validated); // ok ici : c'est Role, pas Permission

        return response()->json($role, 200);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $role->delete(); // les permissions liées partent en cascade (onDelete('cascade'))

        return response()->json(['message' => 'Rôle supprimé avec succès'], 200);
    }

    /**
     * Met à jour toutes les permissions d'un rôle en une seule requête.
     * Attend : { permissions: [ { route_name, create, read, update, delete, access_page }, ... ] }
     */
    public function updatePermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $validated = $request->validate([
            'permissions'               => 'required|array',
            'permissions.*.route_name'  => 'required|string',
            'permissions.*.create'      => 'boolean',
            'permissions.*.read'        => 'boolean',
            'permissions.*.update'      => 'boolean',
            'permissions.*.delete'      => 'boolean',
            'permissions.*.access_page' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $role) {
            foreach ($validated['permissions'] as $perm) {
                // updateOrCreate() évite le piège des méthodes update()/delete() natives d'Eloquent
                Permission::updateOrCreate(
                    [
                        'role_id'    => $role->id,
                        'route_name' => $perm['route_name'],
                    ],
                    [
                        'create'      => $perm['create'] ?? false,
                        'read'        => $perm['read'] ?? false,
                        'update'      => $perm['update'] ?? false,
                        'delete'      => $perm['delete'] ?? false,
                        'access_page' => $perm['access_page'] ?? false,
                    ]
                );
            }
        });

        $role->load('permissions');
        return response()->json($role, 200);
    }

}
