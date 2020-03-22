<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Panier;
use App\Form\PanierType;
use App\Entity\Produit;

class PanierController extends AbstractController
{
    /**
     * @Route("/", name="panier")
     */

    public function index(Request $request){
        $pdo = $this->getDoctrine()->getManager();

        $panier = new Panier();
        $form = $this->createForm(PanierType::class, $panier);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $pdo->persist($panier);    
            $pdo->flush();              
        }
        $paniers = $pdo->getRepository(Panier::class)->findAll();
        $produits = $pdo->getRepository(Produit::class)->findAll();

        return $this->render('panier/index.html.twig', [
            'paniers' => $paniers,
            'produits' => $produits,
        ]);
    }

    /**
     * @Route("/panier/delete/{id}", name="delete_produit_panier")
     */
    public function delete(Panier $panier=null){
        if($panier != null){
            $pdo = $this->getDoctrine()->getManager();
            $pdo->remove($panier);
            $pdo->flush();

            $this->addFlash("success", "Produit supprimée");
        }
        else{
            $this->addFlash("danger", "Produit introuvable");
        }

        return $this->redirectToRoute('panier');
    }

}
