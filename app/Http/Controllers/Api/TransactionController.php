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
/**
     * 
     * @OA\Get(
     *     path="/api/transaction/{id_user}",
     *     tags={"Transação"},
     *     summary="Listar todas as Transações do Usuário Especificado",
     *     description="Usuário pode utilizar para visualizar todas as suas transações.",
     *     operationId="transactionIndex",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Operação Bem Seucedida",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhum Usuário Encontrado"
     *     ),
     * ),
     * 
     * @OA\Post(
     *     path="/api/transaction/{id_user}",
     *     tags={"Transação"},
     *     summary="Cadastrar Transação Para o Usuário Especificado",
     *     description="Usuário pode utilizar para cadastrar uma nova transação na sua conta.",
     *     operationId="transactionStore",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da Categoria",
     *         @OA\JsonContent(
     *              required={"value", "type", "id_category"},
     *              @OA\Property(property="value", type="numeric", example=""),
     *              @OA\Property(property="type", type="string", example=""),
     *              @OA\Property(property="id_category", type="integer", example=""),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Input Inválido",
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transação Adicionada no Banco de Dados",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Algo Errado no Insert"
     *     ),
     * ),
     * 
     * @OA\Delete(
     *     path="/api/transaction/{id_user}/delete/{id_category}",
     *     tags={"Transação"},
     *     summary="Deletar a Transação Especificada do Usuário Especificado",
     *     description="Usuário pode utilizar para deletar uma transação da sua conta.",
     *     operationId="transactionDestroy",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Parameter(
     *         name="id_category",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transação Excluída",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Transação não Encontrado",
     *     ),
     * )
     *    
     * @OA\Get(
     *     path="/api/transaction",
     *     tags={"Transação"},
     *     summary="Listar todas as Transações Existentes",
     *     description="Desenvolvedor pode utilizar para visualizar todas as transações existentes no banco de dados.",
     *     operationId="transactionIndexAdmin",
     *     @OA\Response(
     *         response=200,
     *         description="Operação Bem Seucedida",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Nenhuma Transação Encontrada"
     *     ),
     * ),
*/

    public function index($id_user){
        $transactions = Transaction::where('id_user', '=', $id_user)->get();

        if ($transactions->count()){
            return response()->json([
                'status' => 200,
                'transações' => $transactions
            ], 200);
        }

        else{
            return response()->json([
                'status' => 200,
                'message' => 'Nenhum Transação Realizada'
            ], 200);
        }
    }

    public function store(Request $request, $id_user){
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

        else if (Category::where('id', $request->id_category)->where('id_user', '=', $id_user)->first()){
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
                'status' => 200,
                'message' => "Categoria não Encontrada"
            ], 200);
        }
    }

    public function destroy($id_user, $id_transaction){
        $transaction = Transaction::where('id', $id_transaction)->where('id_user', $id_user)->first();
        if($transaction){
                $transaction->delete();
    
                return response()->json([
                    'status' => 200,
                    'message' => 'Transação Excluída com Sucesso'
                ], 200);
            }
        else{
            return response()->json([
                'status' => 404,
                'message' => 'Transação não Encontrada'
            ], 404);
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
                    'message' => 'Nenhuma Transação Encontrada'
                ], 404);
            }   
    }
}
