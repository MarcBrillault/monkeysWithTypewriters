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

    public function makeFakeText(int $length = 100): void
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

    public function setDev(bool $dev): void
    {
        $this->_dev = $dev;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getCharacterSet(): array
    {
        return str_split($this->_characterSet);
    }

    private function _checkGoogleBooks(): array
    {
        $return = [];
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
            // \Brio\dd($book);

            $return[] = [
                'id'      => $book['id'],
                'title'   => $book['volumeInfo']['title'],
                'authors' => $book['volumeInfo']['authors'],
                'image'   => $book['volumeInfo']['imageLinks']['thumbnail'],
                'link'    => sprintf(self::GOOGLE_LINK, $book['id'], urlencode($this->text)),
            ];
        }

        return $return;
    }

    public function useTypeWriter(): void
    {
        $this->makeFakeText();
        $books = $this->_checkGoogleBooks();
        if (!empty($books)) {
            $this->_sendMail('A monkey has typed something !', $this->_getEmailContent($books));
        }
        \Brio\dd($books);
    }

    private function _sendMail(string $subject, string $content): bool
    {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug  = 4;
        $mail->Host       = getenv('SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username   = getenv('SMTP_USERNAME');
        $mail->Password   = getenv('SMTP_PASSWORD');
        $mail->SMTPSecure = 'tls';
        $mail->Port       = getenv('SMTP_PORT');
        $mail->setFrom(getenv('SMTP_FROM_EMAIL'), getenv('SMTP_FROM_NAME'));
        $mail->addAddress(getenv('SMTP_TO_EMAIL'), getenv('SMTP_TO_NAME'));     // Add a recipient

        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body    = $content;

        if (!$mail->send()) {
            \Brio\dd($mail->ErrorInfo);

            return false;
        }

        return true;
    }

    private function _getEmailContent(array $data): string
    {
        return 'Nope, sorry';
    }
}

class MonkeyException extends Exception
{
}