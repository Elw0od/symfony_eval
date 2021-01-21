<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use App\Form\CategoryType;

class CategoryController extends AbstractController
{
    /**
     * @Route("admin/category", name="category")
     */
    public function index(): Response
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);

        $categoryList = $categoryRepository->findall();
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
            'categoryList' => $categoryList
        ]);
    }

    /**
     * @Route("admin/category/gestion/{slug?}",name="categoryGestion")
     */
    public function gestion(Request $request): Response
    {
    
        $category = new Category();
        //Récupérations de la catégorie d'origine
        if($request->get('slug') != null){
            $categoryRepository = $this->getDoctrine()->getRepository(Category::class);
            $slug = $request->get('slug');
            $category = $categoryRepository->findOneBy(['slug' => $slug]);
        }

        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            //Karen : Amenez-moi le manager!
            $em = $this->getDoctrine()->getManager();
            $em->persist($category);
            $em->flush();

            $this->addFlash('Success','Category added successfully');
            return $this->redirectToRoute('category');
        }

        return $this->render('category/gestion.html.twig',[
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("admin/category/delete/{slug}",name="categoryDelete")
     */
    public function delete(Request $request): RedirectResponse 
    {
        $categoryRepository = $this->getDoctrine()->getRepository(Category::class);
        $categoryEntity = $categoryRepository->findOneBy(['slug'=>$request->get('slug')]);
        
        if(!$categoryEntity){
            $this->addFlash("danger",'Category not found');
            return $this->redirectToRoute('category');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($categoryEntity);
        $em->flush();

        $this->addFlash("warning",'Category deleted successfully');
        return $this->redirectToRoute('category');
    }
}
