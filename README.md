# monkeysWithTypewriters

## Purpose
Based on the [infinite monkey theorem](https://en.wikipedia.org/wiki/Infinite_monkey_theorem),
I wanted to create a script which would generate random strings
and then compare them with existing books.

With enough time and small strings, I was sure I'll get a match sometime.

> The infinite monkey theorem states that a monkey hitting keys at random on a typewriter keyboard for an infinite amount of time will almost surely type a given text, such as the complete works of William Shakespeare.
>
> -- <cite>Wikipedia</cite>

## Configuration

1. Launch the `composer install` command
2. Copy/rename the `.env.example` file to `.env`
3. Create a Google Books API key and copy it to the `.env` file
4. Copy your SMTP server's identifiers to the `.env` file
5. Create a cronjob file which would call the `cronjob.php` file
regularly (Watch for Google API's quota, update `SLEEP_TIME`
and `LOOP_LENGTH` accordingly)
6. ???
7. Profit !

## Profit ?

Well, not really. I wasn't able to tell Google Books API to only
search for perfect matches, and their algorith is _waaaaaaay_ too
permissive, so I've got a "match" nearly each time.

I didn't want to trash the project though, so here it is. Thank me later.
