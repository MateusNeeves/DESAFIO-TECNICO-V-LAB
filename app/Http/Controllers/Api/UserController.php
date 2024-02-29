<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller{
    public function index(){
        $users = User::all();

        if ($users->count()){
            return response()->json([
                'status' => 200,
                'users' => $users
            ], 200);
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'Nenhum Registro Encontrado'
            ], 204);
        }
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'cpf' => 'required|string|digits:11|unique:users',
            'email' => 'required|email|max:400|unique:users',
            'password' => 'required|string|min:8|max:200',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else{
            $user = User::create([
                'name' => $request->name,
                'cpf' => $request->cpf,
                'email' => $request->email,
                'password' => $request->password,
            ]);

            if ($user){
                Category::create([
                    'name' => "Geral",
                    'id_user' => $user->id,
                ]);

                return response()->json([
                    'status' => 201,
                    'message' => "Usuário Criado com Sucesso"
                ], 201);
            }
            else{
                return response()->json([
                    'status' => 500,
                    'message' => "Algo Deu Errado"
                ], 500);
            }
        }
    }

    public function show($id){
        $user = User::find($id);

        if ($user){
            return response()->json([
                'status' => 200,
                'user' => $user
            ], 200);
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'Usuário não Encontrado'
            ], 204);
        }
    }
    
    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'cpf' => 'required|string|digits:11|unique:users',
            'email' => 'required|email|max:400|unique:users',
            'password' => 'required|string|min:8|max:200',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else{
            $user = User::find($id);

            if ($user){

                $user->update([
                    'name' => $request->name,
                    'cpf' => $request->cpf,
                    'email' => $request->email,
                    'password' => $request->password,
                ]);

                return response()->json([
                    'status' => 200,
                    'message' => "Usuário Atualizado com Sucesso"
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 204,
                    'message' => "Usuário não Encontrado"
                ], 204);
            }
        }
    }

    public function destroy($id){
        $user = User::find($id);

        if ($user){
            // DELETE TRANSACTIONS OF USER
            $transactions = Transaction::where('id_user', '=', $id)->get();
            $qty_transac = $transactions->count();

            foreach ($transactions as $transaction)
                $transaction->delete();

            // DELETE CATEGORIES OF USER
            $categories = Category::where('id_user', '=', $id)->get();
            $qty_categ = $categories->count();

            foreach ($categories as $category)
                $category->delete();

            $user->delete();

            return response()->json([
                'status' => 200,
                'message' => 'Usuário, ' . $qty_transac . ' Transações e ' . $qty_categ . ' Categorias Excuídos com Sucesso'
            ], 200);
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'Usuário não Encontrado'
            ], 204);
        }
    }
}
