<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
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
                    'status' => 404,
                    'message' => 'Você Não Realizou Nenhuma Transação'
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
                            'status' => 200,
                            'message' => "Transação Realizada com Sucesso"
                        ], 200);
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
                'status' => 404,
                'message' => 'O usuário de id inserido não existe'
            ], 404);
        }
    }

    public function destroy($id_user, $id_transaction){
        if(User::find($id_user)){
            $transaction = Transaction::find($id_transaction);
            
            if ($transaction && $transaction->id_user == $id_user){
                $transaction->delete();
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Transação Excuída com Sucesso'
                ], 200);
            }
            else{
                return response()->json([
                    'status' => 404,
                    'message' => 'Transação não Encontrada'
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
