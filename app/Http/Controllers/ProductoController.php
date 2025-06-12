<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Producto;
use Illuminate\Support\Facades\Validator;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('productos.index');
    }

    public function list(Request $request){
        $query = Producto::query();

        // Filtro por fecha de ingreso.
        if ($request->has('sort') && $request->sort == 'ingreso') {
            $direccion = $request->get('direction', 'asc');
            $query->orderBy('ingreso', $direccion);
        } else {
            $query->latest();
        }

        $productos = $query->paginate(5);

        return response()->json($productos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->validarProducto($request);

        $producto = new Producto();
        $this->saveProducto($producto, $request);

        return response()->json(['success' => true, 'mensaje' => 'Producto creado exitosamente.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Producto $producto)
    {
        return response()->json($producto);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Producto $producto)
    {
        $this->validarProducto($request, $producto->id);
        $this->saveProducto($producto, $request);

        return response()->json(['success' => true, 'mensaje' => 'Producto actualizado exitosamente.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return response()->json(['success' => true, 'mensaje' => 'Producto eliminado exitosamente.']);
    }

    // Función para validar el producto.

    protected function validarProducto(Request $request, $productoId = null){
        $rules = [
            'codigo' => 'required|alpha_num|unique:productos,codigo' . ($productoId ? ',' . $productoId : ''),
            'nombre' => 'required|regex:/^[\pL\s]+$/u',
            'stock' => 'required|integer|min:1',
            'foto' => 'nullable|mimes:png,jpeg,jpg,gif|max:1536',
            'precio' => 'required|numeric|min:0',
            'ingreso' => 'required|date_format:Y-m-d',
            'expira' => 'required|date_format:Y-m-d|after_or_equal:ingreso'
        ];

        $request->validate($rules);
    }

    // Función para guardar el producto.

    protected function saveProducto(Producto $producto, Request $request){
        $producto->codigo = $request->codigo;
        $producto->nombre = $request->nombre;
        $producto->stock = $request->stock;

        if($request->hasFile('foto')){
            $filename = time() . "_" . $request->file('foto')->getClientOriginalName();
            $request->file('foto')->move(public_path('uploads'), $filename);
            $producto->foto = $filename;
        }

        $producto->precio = $request->precio;
        $producto->ingreso = $request->ingreso;
        $producto->expira = $request->expira;
        $producto->save();
    }
}
