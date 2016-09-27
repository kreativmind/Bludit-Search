# Search for Bludit

#### Instructions:
* Copy search.php to root of Bludit.
* Voila! :D Open domain.com/bludit_directory(if any)/search.php

#### Some additional information:
NOTES:

1. stripos() is used instead of strpos() unlike the snippet for case insensitive search.
2. Warning: This searches through all the entries even the Unpublished ones. Consider to use this only for private purposes.
3. Warning: Code is not audited for security vulnerabilities.
4. The same post/page result is returned if there is more than one instance of the search term found. toDo: Find a way to group them in the same div.
