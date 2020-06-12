<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use App\Service\Helpers;
use App\Entity\Recipe;

class RecipeController extends AbstractController {

    // /**
    //  * @param Request $request
    //  * @param Helpers $helpers
    //  *
    //  * @return Response
    //  * 
    //  * @Author : Olivier Charpentier (olivier.charpentier.dev@gmail.com)
    //  */
    public function recipe(Request $request, $action, Helpers $helpers) {

        /******************** INFORMATIONS *********************/
        // $action = Paramètre de l'URL : "~/recipe/{$action}"
        //
        // Actions disponibles : 
        //    get_recipes => GET
        //    add_recipe => POST
        //    update_recipe => PUT
        //    delete_recipe => DELETE
        //
        // $helpers = Service => réponse au format JSON
        /*******************************************************/

        /******************** FORMAT DE DONNEES ATTENDU PAR L'API *********************/

        // HEADERS : 
        // KEY:"token"
        // VALUE: "iFmCQVshm834k7t39giZjFLNMHwRrTmeihLbvot9RbHC8mjpcP4Uu9TVsFFTR23ESne7bM9zqTk9DWmDrtJuDqLBbfiiLZXRgbYvxac2px3QNEcRNRsQNBdKYqBkzGrg"
        
        /** METHODE POST */
        // {
        //     "title": string,
        //     "subTitle": string, /*Facultatif*/
        //     "ingredients": [
        //         string,
        //         string,
        //         string
        //     ]
        // }
        
        /** METHODE PUT */
        // {
        //     "id":int,    
        //     "title": string,
        //     "subTitle": string, /*Facultatif*/
        //     "ingredients": [
        //         string,
        //         string,
        //         string
        //     ]
        // }
        
        /** METHODE DELETE */
        // {
        //     "id":int   
        // }
        
        /******************************************************************************/

        // Token de l'en-tête http 
        $tokenHeader = $request->headers->get('token');
        $tokenAPI = "iFmCQVshm834k7t39giZjFLNMHwRrTmeihLbvot9RbHC8mjpcP4Uu9TVsFFTR23ESne7bM9zqTk9DWmDrtJuDqLBbfiiLZXRgbYvxac2px3QNEcRNRsQNBdKYqBkzGrg";
        
        // Vérification de la correspondance du token reçu
        if($tokenHeader === $tokenAPI) {

            // Récuperation du JSON
            $json = file_get_contents('php://input');

            // ********************** GET **************************************/
            // Affichage des données méthode GET => URL:  "~/recipe/get_recipes"
            // *****************************************************************/
            if($action === 'get_recipes' && $request->isMethod('get')) {

                $listRecipes = $this->getDoctrine()
                ->getRepository('App:Recipe')
                ->findAll();

                // Message si aucune recette n'est en BDD
                if(count($listRecipes) === 0) {
                    return new Response(
                        "Aucune recette enregistrée",
                        Response::HTTP_OK,
                        array('Content-type' => 'application/json')
                    );
                } else {
                    // Renvoi la liste des recettes (JSON)
                    return $helpers->json($listRecipes);
                }

            } // Fin méthode GET

            // Vérification du JSON
            if($json !== null && $json !== "") {

                // ********************** POST *********************************/
                // Ajout d'une recette => URL: "~/recipe/add_recipe"
                // *************************************************************/
                if($action === 'add_recipe' && $request->isMethod('post')) {

                    $data = $this->JsonData($json);

                    // Verification de la présence de la recette en BDD
                    $findRecipe = $this->getDoctrine()
                        ->getRepository('App:Recipe')
                        ->findBy(array('title' => $data->title));

                    // Réponse si le titre de la recette est déja utilisée
                    if(count($findRecipe) !== 0) {
                        return new Response(
                            'Titre de recette déja utilisé : '.$data->title,
                            Response::HTTP_UNAUTHORIZED,
                            ['content-type' => 'text/html']
                        );
                    } else {
                        // Création d'une recette
                        $newRecipe = new Recipe();
                        $this->createOrUpdateRecipe($newRecipe, $data->title, $data->subTitle, $data->ingredients);

                        return new Response(
                            "Recette ajoutée",
                            Response::HTTP_OK,
                            array('Content-type' => 'application/json')
                        );
                    }
                }// Fin Méthode POST

                 // ********************** PUT **********************************/
                // Modification d'une recette => URL: "~/recipe/update_recipe"
                // **************************************************************/
                if($action === 'update_recipe' && $request->isMethod('put')) {

                    $data = $this->JsonData($json);

                    // Recherche de la recette en BDD
                    $findRecipeId = $this->getDoctrine()
                    ->getRepository('App:Recipe')
                    ->findBy(array('id' => $data->id));

                    // Recherche si le titre est utilisé par une autre recette
                    $findRecipeTitle= $this->getDoctrine()
                    ->getRepository('App:Recipe')
                    ->findBy(array('title' => $data->title));

                    // Recette trouvée en BDD
                    if(count($findRecipeId) !== 0) {

                        // Vérification du titre de la recette à modifier 
                        //=> si il est utilisé par une autre recette que celle à modifier = renvoie une erreur
                        if(count($findRecipeTitle) !== 0 && $findRecipeTitle[0]->getId() !== $data->id) {

                            // Titre de recette déja utilisé 
                            return new Response(
                                'Titre de recette déja utilisé : '.$findRecipeTitle[0]->getTitle(),
                                Response::HTTP_UNAUTHORIZED,
                                ['content-type' => 'text/html']
                            );
                        } else {
                            // Modification recette
                            $updateRecipe = $findRecipeId[0];
                            $this->createOrUpdateRecipe($updateRecipe, $data->title, $data->subTitle, $data->ingredients);

                            return new Response(
                                "Recette modifiée",
                                Response::HTTP_OK,
                                array('Content-type' => 'application/json')
                            );
                        }
                    } else {
                        // Recette inconnue 
                        return new Response(
                            'Recette inconnue => id : '.$data->id,
                            Response::HTTP_UNAUTHORIZED,
                            ['content-type' => 'text/html']
                        );
                    }
                }// Fin méthode PUT
                
                // ********************** DELETE **********************************/
                // Suppression d'une recette => URL: "~/recipe/delete_recipe"
                // **************************************************************/
                if($action === 'delete_recipe' && $request->isMethod('delete')) {

                    $data = $this->JsonData($json);

                    // Recherche de la recette en BDD
                    $findRecipe = $this->getDoctrine()
                    ->getRepository('App:Recipe')
                    ->findBy(array('id' => $data->id));

                    if(count($findRecipe) !== 0) {
                        // Recette trouvée
                        $recipeToDelete = $findRecipe[0];
                        $this->deleteRecipe($recipeToDelete);

                        return new Response(
                            "Recette supprimée",
                            Response::HTTP_OK,
                            array('Content-type' => 'application/json')
                        );

                    } else {
                        // Recette inconnue
                        return new Response(
                            'Recette inconnue => id : '.$data->id,
                            Response::HTTP_UNAUTHORIZED,
                            ['content-type' => 'text/html']
                        );
                    }
                }// Fin méthode DELETE

                // Erreur de méthode
                return new Response(
                    'Erreur URL ou méthode :  URL => '.$action.', Méthode => '.$request->getMethod(),
                    Response::HTTP_UNAUTHORIZED,
                    ['content-type' => 'text/html']
                );
            } else {
                // Erreur de Json
                return new Response(
                    'Erreur Json : '.$json,
                    Response::HTTP_UNAUTHORIZED,
                    ['content-type' => 'text/html']
                );
            }
        } else {
            // Erreur de token header
            return new Response(
                'Erreur token header',
                Response::HTTP_UNAUTHORIZED,
                ['content-type' => 'text/html']
            );
        }
    }

    // Création ou modification d'une recette
    public function createOrUpdateRecipe($Recipe, $title, $subTitle = null, $ingredients) {
        $entityManager = $this->getDoctrine()->getManager();
        $Recipe->setTitle($title);
        $Recipe->setSubTitle($subTitle);
        $Recipe->setIngredients($ingredients);
        $entityManager->persist($Recipe);
        $entityManager->flush();
    }

    // Suppression d'une recette
    public function deleteRecipe($recipe) {
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($recipe);
        $entityManager->flush();
    }

    public function JsonData($json) {
        $jsonDecoded = json_decode($json);
        $id = (isset($jsonDecoded->id)) ? $jsonDecoded->id : null;
        $title = (isset($jsonDecoded->title)) ? $jsonDecoded->title : null;
        $subTitle = (isset($jsonDecoded->subTitle)) ? $jsonDecoded->subTitle : null;
        $ingredients = (isset($jsonDecoded->ingredients)) ? $jsonDecoded->ingredients : null;
        return $jsonDecoded;
    }
}