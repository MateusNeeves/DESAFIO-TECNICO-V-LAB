<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index($id_user){
        if (User::find($id_user)){
            $categories = Category::where('id_user', '=', $id_user)->get();
    
            if ($categories->count()){
                return response()->json([
                    'status' => 200,
                    'categories' => $categories
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Você Não Possui Categorias Cadastradas'
                ], 404);
            }
        }
        else{
            return response()->json([
                'status' => 404,
                'message' => 'O usuário de id inserido não existe'
            ], 404);
        }
    }

    public function store(Request $request, $id_user){
        if (User::find($id_user)){
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:200',
            ]);
    
            if ($validator->fails()){
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            }
            else{
                if(Category::where('name', $request->name)->where('id_user', $id_user)->first()){
                    return response()->json([
                        'status' => 409,
                        'message' => "Essa Categoria já existe"
                    ], 409);
                }
                else{
                    $category = Category::create([
                        'name' => $request->name,
                        'id_user' => $id_user
                    ]);
        
                    if ($category){
                        return response()->json([
                            'status' => 200,
                            'message' => "Categoria Criada com Sucesso"
                        ], 200);
                    }
                    else{
                        return response()->json([
                            'status' => 500,
                            'message' => "Algo Deu Errado"
                        ], 500);
                    }
                }
            }
        }
        else{
            return response()->json([
                'status' => 404,
                'message' => 'O usuário de id inserido não existe'
            ], 404);
        }
    }

    public function destroy($id_user, $id_category){
        if(User::find($id_user)){
            $category = Category::find($id_category);
            
            if ($category && $category->id_user == $id_user){
                // UPDATE TRANSACTIONS OF CATEGORY
                $geral_category = Category::where('id_user', $id_user)->where('name', 'Geral')->get()[0];

                if ($geral_category->id == $id_category){
                    return response()->json([
                        'status' => 404,
                        'message' => 'Acesso Negado. Essa Categoria Não Pode ser Excluída'
                    ], 404);
                }

                $transactions = Transaction::where('id_category', $id_category)->get();
                $qty_transac = $transactions->count();

                foreach ($transactions as $transaction){
                    $transaction->update(['id_category' => $geral_category->id]);
                }

                $category->delete();
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Categoria Excluída e ' . $qty_transac . ' Transações Atualizadas com Sucesso'
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Categoria não Encontrada'
                ], 404);
            }
        }
        else{
            return response()->json([
                'status' => 404,
                'message' => 'O usuário de id inserido não existe'
            ], 404);
        }
    }
}