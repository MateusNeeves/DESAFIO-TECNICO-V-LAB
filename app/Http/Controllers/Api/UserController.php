<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller{


    /**
     * 
     * @OA\Get(
     *     path="/api/user",
     *     tags={"Usuário"},
     *     summary="Listar todos os Usuários",
     *     description="Desenvolvedor pode utilizar para visualizar todos os usuários.",
     *     operationId="userIndex",
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
     *     path="/api/user",
     *     tags={"Usuário"},
     *     summary="Cadastrar Novo Usuário",
     *     description="Desenvolvedo pode utilizar para cadastrar novo usuário.",
     *     operationId="userStore",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do Usuário",
     *         @OA\JsonContent(
     *              required={"name", "cpf", "email", "password"},
     *              @OA\Property(property="name", type="string", example=""),
     *              @OA\Property(property="cpf", type="string", example=""),
     *              @OA\Property(property="email", type="string", example=""),
     *              @OA\Property(property="password", type="string", example=""),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Input Inválido",
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário Adicionado no Banco de Dados",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Algo Errado no Insert"
     *     ),
     * ),
     *
     * @OA\Get(
     *     path="/api/user/{id}",
     *     tags={"Usuário"},
     *     summary="Visualizar um Usuário",
     *     description="Desenvolvedor pode utilizar para visualizar um usuário pelo id.",
     *     operationId="userShow",
     *     @OA\Parameter(
     *         name="id",
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
     *         description="Usuário não Encontrado",
     *     ),
     * )
     * 
     * 
     * @OA\Put(
     *     path="/api/user/edit/{id}",
     *     tags={"Usuário"},
     *     summary="Editar um Usuário",
     *     description="Desenvolvedor pode utilizar para editar um usuário pelo id.",
     *     operationId="userUpdate",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados do Usuário",
     *         @OA\JsonContent(
     *              required={"name", "cpf", "email", "password"},
     *              @OA\Property(property="name", type="string", example=""),
     *              @OA\Property(property="cpf", type="string", example=""),
     *              @OA\Property(property="email", type="string", example=""),
     *              @OA\Property(property="password", type="string", example=""),
     *          ),
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Input Inválido",
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário Atualizado com Sucesso",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não Encontrado",
     *     ),
     * )
     * 
     * @OA\Delete(
     *     path="/api/user/delete/{id}",
     *     tags={"Usuário"},
     *     summary="Deletar um Usuário",
     *     description="Desenvolvedor pode utilizar para deletar um usuário pelo id.Todas as categorias e transações desse usuário serão excluídas.",
     *     operationId="userDestroy",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário e Seus Respectivos Filhos Excluídos",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não Encontrado",
     *     ),
     * )
     * 
     */


    
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
                'status' => 404,
                'message' => 'Nenhum Usuário Encontrado'
            ], 404);
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
                'status' => 404,
                'message' => 'Usuário não Encontrado'
            ], 404);
        }
    }
    
    public function update(Request $request, $id){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:200',
            'cpf' => 'required|string|digits:11',
            'email' => 'required|email|max:400',
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
                if (User::where('cpf', $request->cpf)->where('id', '!=', $id)->first()){
                    return response()->json([
                        'status' => 409,
                        'message' => "Esse CPF já existe."
                    ], 409);
                }
                else if (User::where('email', $request->email)->where('id', '!=', $id)->first()){
                    return response()->json([
                        'status' => 409,
                        'message' => "Esse Email já existe."
                    ], 409);
                }
                else{
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
            }
            else{
                return response()->json([
                    'status' => 404,
                    'message' => "Usuário não Encontrado"
                ], 404);
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
                'status' => 404,
                'message' => 'Usuário não Encontrado'
            ], 404);
        }
    }
}
