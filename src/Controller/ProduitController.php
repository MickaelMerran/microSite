<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Produit;
use App\Form\ProduitType;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use App\Form\PanierType;
use App\Entity\Panier;
use App\Repository\ProduitRepository;



class ProduitController extends AbstractController
{
    /**
     * @Route("/produit", name="produit")
     */
    public function index(Request $request){
        $pdo = $this->getDoctrine()->getManager();

        $produit = new Produit();
        if($produit != null){
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
             
             $fichier = $form->get('photoUpload')->getData();
             
             if($fichier){
                 $nomFichier = uniqid().'.'.$fichier->guessExtension();
 
                 try{
                     
                     $fichier->move(
                         $this->getParameter('upload_dir'),
                         $nomFichier
                     );
                 }
                 catch(FileException $e){
                     $this->addFlash('danger', "Impossible d'uploader le fichier");
                     return $this->redirectToRoute('produit');
                 }
 
                 $produit->setPhoto($nomFichier);
             }

            $pdo->persist($produit);    
            $pdo->flush();
            
            $this->addFlash("success", "Produit créé");
        }
        $produits = $pdo->getRepository(Produit::class)->findAll();
        $paniers = $pdo->getRepository(Panier::class)->findAll();

        return $this->render('produit/index.html.twig', [
            'produits' => $produits,
            'paniers' => $paniers,
            'form_ajout' => $form->createView(),
            ]);
        }
        else{
            $this->addFlash("danger", "Un problème est survenue");
        }
    }
    
    /**
    * @Route("/produit/{id}", name="un_produit")
    */

    public function categorie($id, ProduitRepository $repo, Panier $panier=null, Request $request){
        $produit = $repo->find($id);
        if($produit != null){

        $panier = New Panier();

        $form = $this->createForm(PanierType::class, $panier);
               $form->handleRequest($request);
               if($form->isSubmitted() && $form->isValid()){
                   $panier->setDateAjout(new \DateTime)->setEtat(true);
                   $produit->setPanier($panier);
                   $pdo = $this->getDoctrine()->getManager();
                   $pdo->persist($panier);    
                   $pdo->flush();              

                   $this->addFlash("success", "Produit ajouté au panier");
               }

        return $this->render('produit/produit.html.twig',[
            'produit' => $produit,
            'form_add' => $form->createView(),
        ]);
        }
        else{
            $this->addFlash("danger", "Un problème est survenue");
            return $this->redirectToRoute('produit');
        }
    }

    /**
     * @Route("/produit/delete/{id}", name="delete_produit")
     */
    public function delete(Produit $produit=null){
        if($produit != null){
            // On a trouvé un produit, on le supprime
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($produit);
            $pdo->flush();

            $this->addFlash("success", "Produit supprimée");
        }
        else{
            $this->addFlash("danger", "Produit introuvable");
        }

        return $this->redirectToRoute('produit');
    }
}
