<?php

namespace App\Http\Controllers\Pets;

use App\Http\Controllers\Controller;
use App\Http\Resources\PetsCollection;
use App\Http\Resources\PetsResource;
use App\Models\Pets\Owner;
use App\Models\Pets\Pet;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PetsController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get("search");   //|| ' ' || COALESCE(pets.specie,'') || ' ' || pets.breed"
        $species = $request->get("species");

        $pets = Pet::where(function ($q) use ($search, $species) {
            if ($species) {
                $q->where("specie", "=", $species);
            }

            if ($search) {
                $q->whereHas("owner", function ($q) use ($search) {
                    $q->where(DB::raw("pets.name ||' '|| owners.first_name || ' ' || COALESCE(owners.last_name,'') || ' ' || owners.n_document || '' || owners.phone"), "ilike", "%" . $search . "%");
                });
            }
        })
            ->orderBy("id", "desc")->paginate(20);
        return response()->json([
            "pets" => PetsCollection::make($pets),
            "total_page" => $pets->lastPage(),

        ]);
    }


    public function store(Request $request)
    {
        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("pets", $request->file("imagen"));
            // $request->$request->add(["photo" => $path]);
            $request->merge(["photo" => $path]);
        }

        if ($request->dirth_date) {
            $request->merge(['dirth_date' => Carbon::parse($request->dirth_date)]);
        }

        $owner = Owner::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document,
            'emergency_contact' => $request->emergency_contact,
        ]);

        $request->merge(['owner_id' => $owner->id]);

        $pet =  Pet::create($request->all());

        return response()->json([
            "message" => 200,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pet = Pet::findOrFail($id);
        return response()->json([
            "pet" => PetsResource::make($pet),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $pet =  Pet::findOrFail($id);
        if ($request->hasFile("imagen")) {

            // Si ya existe una imagen de avatar, eliminarla del almacenamiento
            if ($pet->avatar && Storage::exists($pet->avatar)) {
                Storage::delete($pet->avatar);
            }
            // Subir la nueva imagen
            $path = Storage::putFile("pets", $request->file('imagen'));
            // Agregar la ruta de la imagen al request
            $request->merge(["photo" => $path]);
        }

        $pet->update($request->all());

        $owner = $pet->owner();
        $owner->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'type_document' => $request->type_document,
            'n_document' => $request->n_document,
            'emergency_contact' => $request->emergency_contact,
        ]);


        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pet = Pet::findOrFail($id);
        if ($pet->photo && Storage::exists($pet->photo)) {
            Storage::delete($pet->photo);
        }

        $pet->delete();
        return response()->json(
            [
                "message" => 200,
            ]
        );
    }
}
