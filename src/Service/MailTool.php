<?php

namespace App\Service;

use App\Entity\DescripcionDatos;
use App\Form\Model\SoporteDto; 
use App\Enum\EstadoDescripcionDatosEnum;
use Doctrine\ORM\Cache\DefaultCache;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Symfony\Component\Mime\Email;

/*
 * Descripción: Es la utilidad que envía los correos electrónicos 
 *              Se hace cargo de los correos de ayuda soporte y los de workflow
 *              
*/
class MailTool 
{

    private $mailer;
    private $params;

    public function __construct(MailerInterface $mailer,
                                ContainerBagInterface $params)
    {
        $this->mailer = $mailer;
        $this->params = $params;
    }

    /*
     * Descripción: Envía los correos dependiendo del estado del conjunto de datos

     * 
     * Parametros: 
     *          descripcionDatos es el objeto donde esta la información del conjunto datos
     *          comunidado: es el texto que se envía como cuerpo del email. 
     *          Es el texto del formulario que solicita comentarios al cambio de estado
     */
    public function sendEmailWorkFlow(DescripcionDatos $descripcionDatos, 
                                      ?string $comunidado="")
    {
        //el from simpre es el from de la aplicación
        $from = $this->params->get('mailer_sender');
        $subject = "Aragón Open Data";
        $text = "Estado de la solicitud sobre la subida de datos a la plataforma";
        $mensage1 ="";
        $mensage2 ="";
        //si es borrador no hace nada
        if ($descripcionDatos->getEstado()==EstadoDescripcionDatosEnum::BORRADOR){
            return;
        }
        //dependiendo del estado cambia el destinatario 
        switch ($descripcionDatos->getEstado()) {
            case EstadoDescripcionDatosEnum::EN_ESPERA_PUBLICACION:
                $to = $this->params->get('mailer_to_administrator');
                $mensage1 = "Un usuario envío una solicitud de validación a Open Data";
                $mensage2 = " ";
                break;  
            case EstadoDescripcionDatosEnum::EN_ESPERA_MODIFICACION:
                $to = $this->params->get('mailer_to_administrator');
                $mensage1 = "Un usuario envío una solicitud de modificación a Open Data";
                $mensage2 = " ";
                break;            
            case EstadoDescripcionDatosEnum::VALIDADO:
                $to = $descripcionDatos->getUsuario();
                $mensage1 = "Su solicitud de publicación ha sido validada";
                $mensage2 = "Indicaciones por el administrador:";
            case EstadoDescripcionDatosEnum::EN_CORRECCION:
                $to = $descripcionDatos->getUsuario();
                $mensage1 = "Su solicitud de publicación ha sido revisada";
                $mensage2 = "Lea atentamente las indicaciones y corrija. Después envíe de nuevo, su solicitud de validación.";
                break;
            case EstadoDescripcionDatosEnum::DESECHADO:
                $to = $descripcionDatos->getUsuario();
                $mensage1 = "Su solicitud ha sido desechada";
                $mensage2 = "Indicaciones por el administrador:";
                break;
            default:
               break;
        }
        //se envía el email sobre la plantíla
        $email = (new TemplatedEmail())
            ->from($from)
            ->to($to)
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject($subject)
            ->text($text)
            ->htmlTemplate('emails/workflow.html.twig')
            ->context([
                'url' => 'http://personalizacionespre.gnoss.com/plantillas/aragonopendata',
                'mensaje1' =>  $mensage1,
                'mensaje2' =>  $mensage2,
                'mensaje3' =>  $comunidado
            ]);
        $this->mailer ->send($email);

    }
    /*
     * Descripción: Envía los correos de solicitud de soprte

     * 
     * Parametros: 
     *          soporte es el objeto donde esta la información para el correo
     */
    public function sendEmailSoporte(SoporteDto $soporte)
    {
        $from = $soporte->emailContacto;
        $subject = "Aragón Open Data: solicitud soporte  de tipo: " . $soporte->tipoPeticion;
        $to = $this->params->get('mailer_to_administrator');
        $text = $soporte->titulo;
        $nombre = $soporte->nombre;
        $descripcion = $soporte->descripcion;
        
         //se envía el email sobre la plantíla
        $email = (new TemplatedEmail())
        ->from($from)
        ->to($to)
        ->subject($subject)
        ->text($text)
        ->htmlTemplate('emails/soporte.html.twig')
        ->context([
                'url' => 'http://personalizacionespre.gnoss.com/plantillas/aragonopendata',
                'nombre' =>  $nombre,
                'descripcion' => $descripcion
        ]);
        $this->mailer ->send($email);
    }
}