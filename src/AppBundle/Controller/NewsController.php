<?php

namespace AppBundle\Controller;

use AppBundle\Entity\News;
use AppBundle\Form\NewsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/profile", name="profile")
 */
class NewsController extends Controller
{
    /**
     * @Route("/", name="show_news")
     * @Template
     * @param Request $request
     * @return array
     */
    public function showNewsAction(Request $request)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('login'));

        $submit = $request->get('addPost');

        $name = null;
        $description = null;

        if(isset($submit))
        {
            $postName = trim($request->get('name'));
            $postDescription = $request->get('description');
            $userId = $this->getUser()->getId();
            $Session = $this->get('session');

            if ($postName == '' || $postDescription == '') {
                $Session->getFlashBag()->add('warning', 'Name and post cannot be empty.');
                $name = $postName;
                $description = $postDescription;
            }
            else {
                $em = $this->getDoctrine()->getManager();
                $news = new News();
                $news->setName($postName);
                $news->setDescription($postDescription);
                $news->setIsActive(true);
                $news->setCreatedAt(new \DateTime());
                $news->setUpdatedAt(null);
                $news->setAuthorId($userId);

                $em->persist($news);
                $em->flush();

                $Session->getFlashBag()->add('success', 'You succesfully add new post');
            }
        }


//        $Repo = $this->getDoctrine()->getRepository('AppBundle:News');
//        $query = $Repo->createQueryBuilder('n')
//            ->orderBy('n.id', 'DESC')
//            ->getQuery()
//            ->setMaxResults(30);
//        $news = $query->getResult();


        $sql = 'SELECT news.id, news.name, news.description, news.created_at AS createdAt, news.updated_at AS updatedAt, news.author_id AS authorId, users.first_name AS firstName, users.last_name AS lastName
        FROM news INNER JOIN users ON news.author_id = users.id
        WHERE news.is_active = TRUE 
        GROUP BY news.id DESC';

        $em = $this->getDoctrine()->getManager();
        $connection = $em->getConnection();
        $statement = $connection->prepare($sql);
        //$statement->bindValue('id', 123);
        $statement->execute();
        $news = $statement->fetchAll();

        return array(
            'news' => $news,
            'name' => $name,
            'description' => $description
        );
    }


    /**
     * @Route(
     *     "/delete/{id}",
     *     name="news_delete"
     * )
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteNewsAction($id)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('login'));

        $Repo = $this->getDoctrine()->getRepository('AppBundle:News');
        $post = $Repo->find($id);

        if(NULL == $post)
        {
            throw $this->createNotFoundException('Post not found');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Your post was deleted.');

        return $this->redirect($this->generateUrl('show_news'));
    }

    /**
     * @Route(
     *     "/update/{id}",
     *     name="news_update"
     * )
     *
     * @Template
     * @param Request $Request
     * @param $id
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateNewsAction(Request $Request, $id)
    {
        if(!$this->get('security.authorization_checker')->isGranted('ROLE_USER'))
            return $this->redirect($this->generateUrl('login'));

        $Repo = $this->getDoctrine()->getRepository('AppBundle:News');
        $post = $Repo->find($id);

        if(NULL == $post)
        {
            throw $this->createNotFoundException('Post not found');
        }

        $form = $this->createForm(NewsType::class, $post);

        if($Request->isMethod('POST'))
            //if($form->isSubmitted())
        {
            $Session = $this->get('session');
            $form->handleRequest($Request);
            if($form->isValid())
            {
                $post->setUpdatedAt(new \DateTime());
                $em = $this->getDoctrine()->getManager();
                $em->persist($post);
                $em->flush();

                $Session->getFlashBag()->add('success', 'Your post was updated');

                return $this->redirect($this->generateUrl('show_news'));
            }
            else
            {
                $Session->getFlashBag()->add('warning', 'Correct the errors');
            }
        }

        return array(
            'post' => $post,
            'form' => $form->createView()
        );
    }
}
