<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Document\Measurement;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class CollectController extends Controller
{
    /**
     * @Route("/collect")
     */
    public function collectAction(Request $request)
    {   
        $serializer = new Serializer(
            array(new GetSetMethodNormalizer(), new ArrayDenormalizer(), new ObjectNormalizer()),
            array(new JsonEncoder())
        );
        $isMobile = $this->get('mobile_detect.mobile_detector')->isMobile();
        
        // Quelle que qoit la méthode HTTP, on deserialize vers un tableau d'objets Measurement
        if($request->getMethod() === "GET") {
            $measures[] = new Measurement($request->query->all());
        } else if($request->getMethod() === "POST") {
            $measures = $serializer->deserialize($request->getContent(), 'AppBundle\Document\Measurement[]', 'json');
        } else {
            return new Response("Invalid method", Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
        // Validation de chaque objet
        $noError= true;
        $validator = $this->get('validator');
         $dm = $this->get('doctrine_mongodb')->getManager();
        foreach($measures as $m) {
            // Pour les contraintes de validation dépendantes du type d'appareil (mobile ou web)
            $m->setIsMobile($isMobile);
            $v = $validator->validate($m);
            if(count($v)>0) {
                $noError = false;
            } else {
                $dm->persist($m);
                $dm->flush();
            }
            $errors[] = $v;
        }
        
        /* Si au moins une erreur présente, retourne une erreur 400 et un tableau de la forme :
         * [ [],  // Pas d'erreur
         *   [{"property_path":"nom_de_la_propriete_1","message":"message_d_erreur_1"},
         *    {"property_path":"nom_de_la_propriete_2","message":"message_d_erreur_2"}], ...
         *   ],
         *   ...
         * ]
         */
        
        if($noError) {
            return new Response("OK", Response::HTTP_OK);
        } else {
            // FIXME: le serialiseur de base traduit toutes les propriétés de l'objet ConstraintViolation : non désirable
            //$reponse = $serializer->serialize($errors, 'json');
            $reponse = $this->container->get('jms_serializer')->serialize($errors, 'json');
            return new Response($reponse, Response::HTTP_BAD_REQUEST);
        }
 

    }
    

}
