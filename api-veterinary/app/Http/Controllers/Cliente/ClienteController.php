<?php
namespace App\Http\Controllers\Cliente;
use App\Http\Controllers\Controller;
use App\Models\Cliente\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get("search");

        $clients = Cliente::where(DB::raw("nombres ||  ' ' || num_doc"), "ilike", "%" . $search . "%")->orderBy("id", "desc")->get();
      ///  $clients = Cliente::all();
        return response()->json(
            [
                "clients" => $clients->map(function ($client) {
                    return[
                        "id"=>$client->id,
                        "nombres"=>$client->nombres,
                        "apellidos"=>$client->apellidos,
                        "num_doc"=>$client->num_doc,
                        "created_at"=>$client->created_at->format("Y-m-d h:i:s"),
                    ];
                })
            ]
        ); 
        //return response()->json($query->orderBy('id', 'desc')->paginate(10));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
