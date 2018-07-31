<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class IndexController extends Controller
{

    /**
     * @Route("/", name="contact_form")
     *
     * @param Request $request
     * @param \Swift_Mailer $mailer
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request, \Swift_Mailer $mailer)
    {
        $form = $this->createForm(ContactType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $message = (new \Swift_Message($data->getTitle()))
                ->setFrom('send@example.com')
                ->setTo($data->getEmail())
                ->setBody(
                    $data->getContent()
                )
            ;
            $mailer->send($message);

        }

        return $this->render('index/index.html.twig', [
            'contact_form' => $form->createView(),
        ]);
    }
}
