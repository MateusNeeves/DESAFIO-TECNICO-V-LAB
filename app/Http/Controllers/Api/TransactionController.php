<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    public function index($id_user){
        if (User::find($id_user)){
            $transactions = Transaction::where('id_user', '=', $id_user)->get();
    
            if ($transactions->count()){
                return response()->json([
                    'status' => 200,
                    'transações' => $transactions
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 204,
                    'message' => 'Você Não Realizou Nenhuma Transação'
                ], 204);
            }
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'O usuário de id inserido não existe'
            ], 204);
        }
    }

    public function store(Request $request, $id_user){
        if (User::find($id_user)){
            $validator = Validator::make($request->all(), [
                'value' => 'required|numeric|between:0,999999.99',
                'type' => 'required|string|max:10',
                'id_category' => 'required|numeric',
            ]);
    
            if ($validator->fails()){
                return response()->json([
                    'status' => 422,
                    'errors' => $validator->messages()
                ], 422);
            }
            else{
                if (Category::where('id', '=', $request->id_category)->where('id_user', '=', $id_user)->first()){
                    $transaction = Transaction::create([
                        'value' => $request->value,
                        'type' => $request->type,
                        'id_user' => $id_user,
                        'id_category' => $request->id_category,
                    ]);
        
                    if ($transaction){
                        return response()->json([
                            'status' => 201,
                            'message' => "Transação Realizada com Sucesso"
                        ], 201);
                    }
                    else{
                        return response()->json([
                            'status' => 500,
                            'message' => "Algo Deu Errado"
                        ], 500);
                    }
                }
                else{
                    return response()->json([
                        'status' => 500,
                        'message' => "ID de categoria errado"
                    ], 500);
                }
            }
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'O usuário de id inserido não existe'
            ], 204);
        }
    }

    public function destroy($id_user, $id_transaction){
        if(User::find($id_user)){
            $transaction = Transaction::find($id_transaction);
            
            if ($transaction && $transaction->id_user == $id_user){
                $transaction->delete();
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Transação Excluída com Sucesso'
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 400,
                    'message' => 'Transação não Encontrada'
                ], 400);
            }
        }
        else{
            return response()->json([
                'status' => 204,
                'message' => 'O usuário de id inserido não existe'
            ], 204);
        }
    }

    public function indexAdmin(Request $request){
        $category = '%'.$request->category.'%';
        $user = '%'.$request->user.'%';
        $type = '%'.$request->type.'%';

        $transactions = collect(DB::select('SELECT * 
                                            FROM TRANSACTIONS 
                                            WHERE ID_CATEGORY IN (SELECT ID 
                                                                  FROM CATEGORIES
                                                                  WHERE NAME LIKE ?)
                                            AND ID_USER IN (SELECT ID
                                                            FROM USERS
                                                            WHERE NAME LIKE ?)
                                            AND TYPE LIKE ?', [$category, $user, $type]));

            if ($transactions->count()){
                return response()->json([
                    'status' => 200,
                    'transações' => $transactions
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Nenhuma Transação Feita'
                ], 404);
            }   
    }
}
