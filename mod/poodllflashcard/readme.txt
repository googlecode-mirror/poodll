Reviewed version for over 1.8.
Complete redraw.
Valery Fremaux 2008

Upgrades to 2008051100

USING THE Flashcard Activity
-----------------------------

1. Unzip the archive and read this file  ;-)

2. Make a new directory called poodllflashcard in your
   modules directory. Put the files from this zip 
   into that directory.

3. Visit the admin page and your module should be 
   noticed and registered as a new entry in the 
   table "modules". This will also update the 
   tables for the matching questions.

4. Go into the quiz module and make some matching 
   questions.

5. Create a new poodllflashcard activity. Select the 
   matching quiz question you want to use for importing.
   Force import when creating.

Major features
--------------

Now poodllflashcard is completely autonomous module, except when importing a first set
of questions. As the poodllflashcard module do provide in this version a complete editor for 
questions/answer couples, there is no absolute need to use a matching question.

The poodllflashcard implements two play modes : 

- Freeplay mode : 

The users can use a single deck and rotate into cards for helping getting answers in memory.
This mode is similar to the old version (Gustav Delius') of poodllflashcard. There is no instance 
memory of what is done in the deck.

The freeplay can be disabled by configuration.

- Leitner play mode

The users may use 2, 3 or 4 decks with a schedule for review that is calculated automatically.
The cards will propagate from most difficult decks to easier decks, as good answers are checked 
in by the users. The cards may backpropagate if "autodowngrade" mode is enabled and the review is not
performed in required time.

-- Summaries

Teachers now can access a summary of the game, to check user's assiduity and lateness.

Card management and multimedia aspects
-------------------------------------

Cards are edited as a set of quesion/answer couples. Question and answers can be flipped so
that questions are used as answers and answers as questions.

Any question or answer can be set a media mode. This poodllflashcard version supports text cards, 
but also image cards and sound cards. For using images or sounds, just upload necessary files
where the file chooser will suggest it (you'll find a file chooser for each non text media 
input in the card editor). Then let the file name as it returns from the file chooser.

Handling manually file names could files be shared in the same course scope.

The poodllflashcard can thus be used to construct :

Text -> Text
Text -> Image
Image -> Text
Image -> Image
Sound -> Image
Image -> Sound
Text -> Sound
Sound -> Text
Sound -> Sound

quizzes.

Adds-on to the new design : poodllflashcard "by instance" customization
------------------------------------------------------------------

The poodllflashcard can now have customizable backgrounds and stylesheet, using
a poodllflashcard.css clone in the course files.

Once the module is instanciated, copy the flashcard.css file within
<MOODLEDATA>/<courseid>/moddata/flashcard/<instanceid>/poodllflashcard.css and change this
file to what you expect.

