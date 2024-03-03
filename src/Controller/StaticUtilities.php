<?php

namespace App\Controller;

use App\Entity\User;
use PhpParser\Node\Expr\Cast\Bool_;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class StaticUtilities
{
    /*******************/
    /*** FILES PATHS ***/
    /*******************/
    public static $USER_IMG_PATH = "img/users/";
    public static $ARCHIVOS_DOCS_PATH = "archivos_docs/";

    /************************/
    /*** IMAGE PROPERTIES ***/
    /************************/
    private static $ANCHO_IMG_MAX = 500;

    /**
     * Parses the active status and returns a corresponding string value.
     *
     * @param bool $status The active status to be parsed.
     * @return string The parsed active status as a string ('Activo' or 'Inactivo').
     */
    public static function parseActiveStatus($status)
    {
        $parsedStatus = $status;
        switch ($status) {
            case true:
                $parsedStatus = 'Activo';
                break;
            case false:
                $parsedStatus = 'Inactivo';
                break;
        }

        return $parsedStatus;
    } 

    /*****************/
    /*** MAIL DATA ***/
    /*****************/
 
    public static $WELCOME_MAIL_SUBJECT = "¡Le damos la bienvenida!";
   
    public static $WELCOME_BRAIN_MAIL_SUBJECT = "Bienvenido al cerebro de API-Notes";


    /***************************************/
    /*** ENVIRONMENT DEPENDENT VARIABLES ***/
    /***************************************/
    public static $PRODUCTION = false;
    public static $DEV_URL = "URL_DESARROLLO";
    public static $PROD_URL = "URL_PRODUCCION";

    /**
     * Dependiendo de si la variable de "PRODUCTION" está a true o false,
     * devolvemos la URL de producción o de SiteGround
     *
     * @return [type]
     */
    public static function getURL()
    {
        if (self::$PRODUCTION) {
            return self::$PROD_URL;
        }

        return self::$DEV_URL;
    }
    
    public static function getAlias()
    {
        $ADMIN_MAIL_DIR = "projectmailvdv@gmail.com";
        return $ADMIN_MAIL_DIR;
    }

    /**
     * Send email to user with subject and html body
     * 
     * @param mixed $to
     * @param mixed $subject
     * @param mixed $htmlBody
     * 
     * @return [type]
     */
    public static function sendEmail($to, $subject, $htmlBody, $cc = 'false')
    {
        $email = (new Email())
            ->from(self::getAlias())
            ->subject($subject)
            ->html($htmlBody);

        if (is_array($to)) {
            $email->bcc(...$to);
        } else {
            $email->to($to);
        }
        if ($cc != 'false') {
            $email->cc($cc);
        };

        $mailer = new Mailer(Transport::fromDsn($_ENV['MAILER_DSN']));

        try {
            $mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            return $e->getMessage();
        }

        return true;
    }


    public static function getRecoverEmailBody($nombre, $url_btn, $year)
    {
        $mailBodyFile = fopen(realpath("./email_template/recover_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./email_template/recover_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NAME_-", $nombre, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        $body = str_replace("-_YEAR_-", $year, $body);
        return $body;
    }

    public static function getWelcomeEmailBody($nombre, $cargo, $password, $url_btn)
    {
        $mailBodyFile = fopen(realpath("./welcome_mail_body.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./welcome_mail_body.html")));
        fclose($mailBodyFile);
        $body = str_replace("-_NOMBRE_-", $nombre, $body);
        $body = str_replace("-_CARGO_-", $cargo, $body);
        $body = str_replace("-_PASSWORD_-", $password, $body);
        $body = str_replace("-_URLBTN_-", $url_btn, $body);
        return $body;
    }

    public static function getWelcomeBrainBody($url, $anio)
    {
        $mailBodyFile = fopen(realpath("./email_template/welcome_brain_user.html"), "r") or die("Unable to open file!");
        $body = fread($mailBodyFile, filesize(realpath("./email_template/welcome_brain_user.html")));
        fclose($mailBodyFile); 
        $body = str_replace("-_URLBTN_-", $url, $body);
        $body = str_replace("-_ANIO_-", $anio, $body);
        return $body;
    } 

    /***********************/
    /*** DATA VALIDATION ***/
    /***********************/
    public static function dataIsValid($data)
    {
        switch (gettype($data)) {
            case 'string':
                return isset($data) && !empty($data);
                break;
            case 'boolean':
            case 'integer':
            case 'double':
                return !is_null($data);
                break;
            case 'array':
                return isset($data);
                break;
            case 'NULL':
                return false;
                break;
            default:
                return isset($data);
                break;
        }
    }

    public static function arrayOfIdsIsValid($array)
    {
        if (gettype($array) != 'array') {
            return false;
        }
        if (sizeof($array) == 0) {
            return false;
        }
        foreach ($array as $element) {
            if (!is_numeric($element)) {
                return false;
            }
        }
        return true;
    }

    public static function colorIsValid($color)
    {
        $validCharacters = str_split('#0123456789ABCDEF');
        $isString = gettype($color) == 'string';
        $formatIsValid = true;
        $splittedColor = str_split(strtoupper($color));
        foreach ($splittedColor as $colorCharacter) {
            $formatIsValid = $formatIsValid && in_array($colorCharacter, $validCharacters);
        }
        $formatIsValid = $formatIsValid && sizeof($splittedColor) == 7;
        return $isString && $formatIsValid;
    }

    public static function dateIsValid($date, $format = 'Y/m/d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    public static function validateDontDuplicateArray($array)
    {
        $uniqueArray = array_unique($array);

        if (count($array) === count($uniqueArray)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Comprueba que la fecha inicial y final son válidas, además de que fecha inicial no puede ser después de fecha fin
     *
     * @param mixed $initialDate
     * @param mixed $finalDate
     *
     * @return [type]
     */
    public static function checkCorrectDates($initialDate, $finalDate)
    {
        if (!self::dateIsValid($initialDate)) {
            return "Fecha inicial no válida";
        }
        if (!self::dateIsValid($finalDate)) {
            return "Fecha final no válida";
        }

        if ($initialDate > $finalDate) {
            return "Periodo de fechas inconsistente";
        }

        return  "";
    }

    public static function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public static function randomDigitsPassword()
    {
        $alphabet = '1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 7; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    /**
     * Comprueba que el base64 enviado es el de una imagen
     *
     * @param mixed $base64_string
     * @param mixed $id_archivo
     *
     * @return [type]
     */
    public static function checkImagen($base64_string, $id_archivo)
    {
        $extension = self::string_between_two_string($base64_string, 'image/', ';base64');
        if (!$extension) {
            return false;
        }

        $file = 'temp' . $id_archivo . '.' . $extension;
        $tmp = fopen($file, "wb");
        $data = explode(',', $base64_string);

        if (!$data || count($data) < 2) {
            return false;
        }

        $written = fwrite($tmp, base64_decode($data[1]));
        fclose($tmp);

        if (!$written) {
            return false;
        }
        if (filesize($file) > 1048576) { // 1MB
            return false;
        }
        $path_info = pathinfo($file);
        switch ($path_info['extension']) {
            case "jpg":
            case "JPG":
            case "jpeg":
            case "JPEG":
            case "png":
            case "PNG":
            case "gif":
            case "GIF":
            case "bmp":
            case "BMP":
                return $file;
                break;
            default:
                return false;
        }
    }

    /**
     * Guardar archivo imagen en esa ruta especificada
     *
     * @param mixed $file
     * @param mixed $path
     *
     * @return [type]
     */
    public static function setImagen($file, $path)
    {
        try {
            $path_info = pathinfo($file);
            switch ($path_info['extension']) {
                case "jpg":
                case "JPG":
                case "jpeg":
                case "JPEG":
                    $imagen = imagecreatefromjpeg($file);
                    break;
                case "png":
                case "PNG":
                    $imagen = imagecreatefrompng($file);
                    break;
                case "gif":
                case "GIF":
                    $imagen = imagecreatefromgif($file);
                    break;
                case "bmp":
                case "BMP":
                    $imagen = imagecreatefromwbmp($file);
                    break;
            }
            $sizes = getimagesize($file);
            if ($sizes[0] > self::$ANCHO_IMG_MAX) {
                $nuevo_ancho = self::$ANCHO_IMG_MAX;
                $nuevo_alto = self::$ANCHO_IMG_MAX * $sizes[1] / $sizes[0];
            } else {
                $nuevo_ancho = $sizes[0];
                $nuevo_alto = $sizes[1];
            }
            $thumb = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 255, 255, 255, 127);
            imagefilledrectangle($thumb, 0, 0, $nuevo_ancho, $nuevo_alto, $transparent);

            imagecopyresampled($thumb, $imagen, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $sizes[0], $sizes[1]);
            unlink($file);

            return imagepng($thumb, $path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param mixed $str
     * @param mixed $starting_word
     * @param mixed $ending_word
     *
     * @return [type]
     */
    public static function string_between_two_string($str, $starting_word, $ending_word)
    {
        $subtring_start = strpos($str, $starting_word);
        $subtring_start += strlen($starting_word);
        $size = strpos($str, $ending_word, $subtring_start) - $subtring_start;
        return substr($str, $subtring_start, $size);
    }

    /**
     *
     * Recoge imágenes en base64, les asigna un nombre y numeración
     *
     * @param mixed $image
     *
     * @return [type]
     */
    public static function transformAndSaveImage($image, $path, $name)
    {
        if ($image) {
            $finalUrl = self::uploadImage($image, $path, $name, bin2hex(random_bytes(5)));
            if ($finalUrl) {
                return $finalUrl;
            } else {
                return false;
            }
        }
    }


    /**
     * Uploads the base 64 image to the specified file path using a file name and
     * an id for creating unique file names (file1.png)
     *
     * @param mixed $base64Request
     * @param mixed $filePath
     * @param mixed $fileName
     * @param mixed $id
     *
     * @return [type]
     */
    public static function uploadImage($base64Request, $filePath, $fileName, $id)
    {
        if (self::dataIsValid($base64Request)) {
            $file = self::checkImagen($base64Request, $fileName . $id);
            if ($file) {
                // Crear carpeta si no existe
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0777, true);
                }
                $relativeUrl = $filePath . $fileName . $id . ".png";
                self::setImagen($file, $relativeUrl);
                return self::getServerPublicFolder() . '/' . $relativeUrl;
            } else {
                return false;
            }
        }
    }

    /**
     * Returns the server public folder (http://localhost/proyecto/public)
     *
     * @return string
     */
    public static function getServerPublicFolder(): string
    {

        $cwd = str_replace("\\", '/', getcwd());
        $cwd = explode("/", $cwd);
        $documentRoot = str_replace("\\", '/', $_SERVER['DOCUMENT_ROOT']);
        $documentRoot = explode("/", $_SERVER['DOCUMENT_ROOT']);

        $relative = [];
        foreach (array_reverse($cwd) as $currentDir) {
            if ($currentDir == end($documentRoot)) {
                break;
            }
            array_unshift($relative, $currentDir);
        }
        $relative = implode("/", $relative);

        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/" . $relative;
    }

    /**
     * @param mixed $urlImage
     *
     * @return [type]
     */
    public function convertUrFileToBase64($urlImage)
    {
        // Get the image and convert into string
        $img = file_get_contents($urlImage);

        // Encode the image string data into base64
        return base64_encode($img);
    }

    /**
     *
     * Recoge pdf en base64, les asigna un nombre y numeración
     *
     * @param mixed $pdf
     *
     * @return [type]
     */
    public static function transformAndSavePdf($pdf, $ruta, $name)
    {
        if ($pdf) {
            $urlFinal = self::uploadPdf($pdf, $ruta, $name, bin2hex(random_bytes(5)));
            if ($urlFinal) {
                return $urlFinal;
            } else {
                return false;
            }
        }
    }

    /**
     * Uploads the base 64 pdf to the specified file path using a file name and
     * an id for creating unique file names (file1.pdf)
     *
     * @param mixed $base64Request
     * @param mixed $filePath
     * @param mixed $fileName
     * @param mixed $id
     *
     * @return [type]
     */
    public static function uploadPdf($pdf, $filePath, $fileName, $id)
    {
        if (self::dataIsValid($pdf)) {
            $file = self::checkPdf($pdf, $fileName . $id);
            if (substr($file, 0, 4) === 'temp') {
                // Crear carpeta si no existe
                if (!is_dir($filePath)) {
                    mkdir($filePath, 0775, true);
                }
                $urlFinal = $filePath . $fileName . $id . ".pdf";
                rename($file, $urlFinal);
                return self::getServerPublicFolder() . '/' . $urlFinal;
            } else {
                return $file;
            }
        }
    }

    /**
     * Comprueba que el base64 enviado es el de un pdf
     *
     * @param mixed $base64_string
     * @param mixed $id_archivo
     *
     * @return [type]
     */
    public static function checkPdf($base64_string, $id_archivo)
    {
        $extension = self::string_between_two_string($base64_string, 'application/', ';base64');
        if (!$extension) {
            return false;
        }

        $file = 'temp' . $id_archivo . '.' . $extension;
        $tmp = fopen($file, "wb");
        $data = explode(',', $base64_string);

        if (!$data || count($data) < 2) {
            return false;
        }

        $written = fwrite($tmp, base64_decode($data[1]));
        fclose($tmp);

        if (!$written) {
            return false;
        }
        if (filesize($file) > 10048576) { // 1MB
            return false;
        }
        $path_info = pathinfo($file);
        switch ($path_info['extension']) {
            case "pdf":
            case "PDF":
                return $file;
                break;
            default:
                return false;
        }
    }

    public static function getToken()
    {
        $token = str_replace(' ', '+', $_GET['token']);
        return $token;
    }
 
}
