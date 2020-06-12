# Recipe-API

<strong>API CRUD Symfony 5.1 de gestion de recettes</strong>

# <strong>Identification <br></strong>
<strong>Headers:</strong><br>
    
    key : "token"
    value : iFmCQVshm834k7t39giZjFLNMHwRrTmeihLbvot9RbHC8mjpcP4Uu9TVsFFTR23ESne7bM9zqTk9DWmDrtJuDqLBbfiiLZXRgbYvxac2px3QNEcRNRsQNBdKYqBkzGrg

# <strong>Méthodes</strong><br>
  <strong>GET</strong> (Récupère la liste des recettes)<br>
  
  
    Url : "~/recipe/get_recipes"
    
    Response : Json
    {
      "id":int,    
      "title": string,
      "subTitle": string, 
      "ingredients": [
          string,
          string,
          string
      ]
    }
    
  <strong>POST</strong> (Ajoute une recette)<br>
  
  
    Url : "~/recipe/add_recipe"
    
    Body : Json
    {
      "title": string,
      "subTitle": string, 
      "ingredients": [
          string,
          string,
          string
      ]
    } 
    
  <strong>PUT</strong> (Modifie une recette)<br>
  
  
    Url : "~/recipe/update_recipe"
    
    Body : Json
    {
      "id":int,    
      "title": string,
      "subTitle": string, 
      "ingredients": [
          string,
          string,
          string
      ]
    }    
  

  <strong>DELETE</strong> (Supprime une recette)<br>
  
  
    Url : "~/recipe/delete_recipe"
    
    Body : Json
    {
      "id":int   
    } 
 
  
