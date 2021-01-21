<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Product;
use App\Form\ProductType;

class ProductController extends AbstractController
{
    /**
     * @Route("admin/product", name="product")
     */
    public function index(): Response
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products
        ]);
    }

   /**
     * @Route("admin/product/gestion/{slug?}",name="productGestion")
     */
    public function gestion(Request $request): Response
    {
    
        $product = new Product();
        //Récupérations de la catégorie d'origine
        if($request->get('slug') != null){
            $productRepository = $this->getDoctrine()->getRepository(Product::class);
            $slug = $request->get('slug');
            $product = $productRepository->findOneBy(['slug' => $slug]);
        }

        $form = $this->createForm(ProductType::class,$product);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            //Karen : Amenez-moi le manager!
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();

            $this->addFlash('success','Product added successfully');
            return $this->redirectToRoute('product');
        }

        return $this->render('product/gestion.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("admin/product/delete/{slug}",name="productDelete")
     */
    public function delete(Request $request): RedirectResponse 
    {
        $productRepository = $this->getDoctrine()->getRepository(Product::class);
        $ProductEntity = $productRepository->findOneBy(['slug'=>$request->get('slug')]);
        
        if(!$ProductEntity){
            $this->addFlash("danger",'Product not found');
            return $this->redirectToRoute('product');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($ProductEntity);
        $em->flush();

        $this->addFlash("warning",'Product successfully deleted');
        return $this->redirectToRoute('product');
    }
}
