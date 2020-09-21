# Configure your own tracking objects

To configure your own tracking objects, you just need to add some setup-typoScript.
The TypoScript definition consists of two parts which both go in the root template.

The first part is the *type* definition. The *type* definition tells the backend module that there is a new tracking
object, eg. it registers the select box entry field.

As an example, the *type* definition for the Questions/FAQ extension looks as follows:

```typo3_typoscript
plugin.tx_viewstatistics.settings.types {
	# The following key must be equal
	# with the database table name
	# of object which we want to track
	tx_questions_domain_model_question {
		# The following label will be used for:
		# Select boxes, Table headers, and more
		label = Questions/FAQ
		# This field identifier refers to the
		# database field, the content of which
		# is used to display tracking rows
		# in the backend
		field = title
		# The repository setting points to a
		# Repository class (including namespace),
		# used to select data in the
		# backend module
		repository = CodingMs\Questions\Domain\Repository\QuestionRepository
		# Extension key of the tracking object.
		# If the tracking object is a part of the
		# TYPO3 core, just enter `core`.
		extensionKey = questions
	}
}
```

The second part is the *object* definition. The *object* definition defines which request parameter creates the
tracking information.

If we take a look at the query parameters in our example:

`?tx_questions_questions[question]=1&tx_questions_questions[action]=show&cHash=fb3dd90304ba52588593187b1c8aac3d`

we can see the necessary parameter that contains the *uid* of the record we want to track - this is

`tx_questions_questions[question]=1`

The *object* definition of our Questions/FAQ extension then looks like this:

```typo3_typoscript
plugin.tx_viewstatistics.settings.objects {
	// First we have main variable of the parameter
	tx_questions_questions {
		// Next the array key of the parameter
		question {
			# The label is also used for:
			# Select boxes, Table header, and more
			label = Questions/FAQ
			# The table is the database table
			# which is related to the parameter uid
			table = tx_questions_domain_model_question
			# The title is the database field
			# name, from which the title is
			# read.
			title = title
		}
	}
}
```

Finally, ensure that the new configuration is also available in the backend module:

```typo3_typoscript
module.tx_viewstatistics < plugin.tx_viewstatistics
```

There are some more configuration examples that you can look at in *ext_typoscript_setup.txt*
in the root path of the view_statistics extension.
