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
    /**
     * 
     * @OA\Get(
     *     path="/api/category/{id_user}",
     *     tags={"Categoria"},
     *     summary="Listar todas as Categorias do Usuário Especificado",
     *     description="Usuário pode utilizar para visualizar todas as suas categorias.",
     *     operationId="categoryIndex",
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
     *         description="Nenhuma Categoria Encontrado Para Esse Usuário"
     *     ),
     * ),
     * 
     * @OA\Post(
     *     path="/api/category/{id_user}",
     *     tags={"Categoria"},
     *     summary="Cadastrar Nova Categoria Para o Usuário Especificado",
     *     description="Usuário pode utilizar para cadastrar uma nova categoria na sua conta.",
     *     operationId="categoryStore",
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
     *              required={"name"},
     *              @OA\Property(property="name", type="string", example=""),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Input Inválido",
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Categoria Já Existe",
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Categoria Adicionada no Banco de Dados",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Algo Errado no Insert"
     *     ),
     * ),
     * 
     * @OA\Delete(
     *     path="/api/category/{id_user}/delete/{id_category}",
     *     tags={"Categoria"},
     *     summary="Deletar a Categoria Especificada do Usuário Especificado",
     *     description="Usuário pode utilizar para deletar uma categoria da sua conta. Todas as transações dessa categoria apagada são alteradas para categorial 'Geral'.",
     *     operationId="categoryDestroy",
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
     * 
     *     @OA\Response(
     *         response=405,
     *         description="Acesso Negado. Categoria 'Geral' não pode ser excluída.",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Categoria Excluída e transações atualizadas.",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Categoria não Encontrado",
     *     ),
     * )
     *     
*/


    public function index($id_user){
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
                'message' => 'Nenhuma Categoria Encontrada'
            ], 404);
        }
    }

    public function store(Request $request, $id_user){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
        ]);

        if ($validator->fails()){
            return response()->json([
                'status' => 422,
                'errors' => $validator->messages()
            ], 422);
        }
        else if (Category::where('name', $request->name)->where('id_user', $id_user)->first()){
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
                    'status' => 201,
                    'message' => "Categoria Criada com Sucesso"
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

    public function destroy($id_user, $id_category){
        $category = Category::where('id', $id_category)->where('id_user', $id_user)->first();
        if($category){
            // UPDATE TRANSACTIONS OF CATEGORY
            $geral_category = Category::where('id_user', $id_user)->where('name', 'Geral')->get()[0];

            if ($geral_category->id == $id_category){
                return response()->json([
                    'status' => 405,
                    'message' => 'Acesso Negado. Essa Categoria Não Pode ser Excluída'
                ], 405);
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
}
