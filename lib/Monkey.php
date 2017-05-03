<?php

class Monkey
{
    /**
     * @var string
     */
    public $text = '';

    /**
     * @var bool
     */
    private $_dev = false;

    /**
     * @var string
     */
    private $_characterSet = 'abcdefghijklmnopqrstuvwxyz ';

    private $_testValues = [
        "Grâce à vous une robe a passé dans ma vie.",
        "Eh bien! oui, c'est mon vice. Déplaire est mon plaisir. J'aime qu'on me haïsse.",
        "Pour un oui, pour un non, se battre, - ou faire un vers!",
        "Sois satisfait des fleurs, des fruits, même des feuilles, Si c'est dans ton jardin à toi que tu les cueilles!",
        "Ne pas monter bien haut, peut-être, mais tout seul!",
        "On n'abdique pas l'honneur d'être une cible",
    ];

    const GOOGLE_LINK = 'https://books.google.fr/books?hl=fr&id=%1$s&q=%2$s#v=snippet&q=%2$s&f=false';

    /**
     * @param int $length
     */
    public function makeFakeText($length = 100)
    {
        if (!empty($this->text)) {
            return;
        }

        if ($this->_dev) {
            $randIndex  = array_rand($this->_testValues);
            $this->text = $this->_testValues[$randIndex];

            return;
        }

        $text = '';

        $characterSet = $this->getCharacterSet();
        while (strlen($text) < $length) {
            $randIndex = array_rand($characterSet);
            $text      .= $characterSet[$randIndex];
        }

        $this->text = $text;
    }

    /**
     * @param bool $dev
     */
    public function setDev($dev)
    {
        $this->_dev = $dev;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getCharacterSet()
    {
        return str_split($this->_characterSet);
    }

    /**
     * @return array
     */
    private function _checkGoogleBooks()
    {
        $return = [
            'text' => $this->text,
        ];
        $client = new Google_Client();
        $client->setApplicationName(getenv('GOOGLE_BOOKS_PROJECT_NAME'));
        $client->setDeveloperKey(getenv('GOOGLE_BOOKS_API_KEY'));

        $service = new Google_Service_Books($client);
        $client->setUseBatch(true);
        $batch = $service->createBatch();
        $req1  = $service->volumes->listVolumes($this->text);
        $batch->add($req1, "test");
        $results = $batch->execute();

        foreach ($results['response-test'] as $book) {

            $return['books'][] = [
                'id'      => $book['id'],
                'title'   => $book['volumeInfo']['title'],
                'authors' => $book['volumeInfo']['authors'],
                'image'   => $book['volumeInfo']['imageLinks']['thumbnail'],
                'link'    => sprintf(self::GOOGLE_LINK, $book['id'], urlencode($this->text)),
            ];
        }

        return $return;
    }

    /**
     *
     */
    public function useTypeWriter()
    {
        $this->makeFakeText(getenv('TEXT_LENGTH'));
        $books = $this->_checkGoogleBooks();
        // \Brio\dd($books);
        if (!empty($books['books'])) {
            $this->_sendMail('A monkey has typed something !', $this->_getEmailContent($books['text'], $books['books']));
        }
    }

    /**
     * @param string $subject
     * @param string $content
     * @return bool
     */
    private function _sendMail($subject, $content)
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        // $mail->SMTPDebug  = 4;
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = getenv('SMTP_PORT');
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
        $mail->addAddress(getenv('SMTP_TO_EMAIL'), getenv('SMTP_TO_NAME'));

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $content;

        if (!$mail->send()) {

            return false;
        }

        return true;
    }

    /**
     * @param string $text
     * @param array  $books
     * @return string
     */
    private function _getEmailContent($text, array $books)
    {
        $return = sprintf('Texte recherché : %s<br>', $text);
        $html   = <<<HTML
<h1>%s</h1>
Par %s<br>
<img src="%s"><br>
<a href="%s">Texte complet</a>
HTML;
        foreach ($books as $book) {
            $authors = '';
            if (!empty($book['authors'])) {
                $authors = implode(', ', $book['authors']);
            }
            $return .= sprintf(
                $html,
                $book['title'],
                $authors,
                $book['image'],
                $book['link']
            );
        }

        return $return;
    }
}

class MonkeyException extends Exception
{
}