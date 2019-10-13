# Configure own tracking objects

For configuring your own tracking objects, you just need a bit Setup-TypoScript. The TypoScript-Definition is separated in two parts, which must be placed in the Root-Template.

First we have the definition of the *type*. The *type* definition is required for teaching the backend module, that there is a new tracking object. It registers a select box entry an so on. A *type* definition for our Questions/FAQ extension looks like:

```typo3_typoscript
plugin.tx_viewstatistics.settings.types {
	# The following key must be equal
	# with the database table name
	# of object which we want to track
	tx_questions_domain_model_question {
		# The following label will be used for:
		# Select boxes, Table header, and more
		label = Questions/FAQ
		# This field identifier referes to the
		# database field, which content should 
		# be used for displaying tracking rows
		# in the backend
		field = title
		# The repository setting points to a
		# Repository class (including namespace),
		# which is used to selecting data in the
		# backend module
		repository = CodingMs\Questions\Domain\Repository\QuestionRepository
		# Extension key of the tracking object.
		# If the tracking object is a part of the
		# TYPO3 core, just enter `core`.
		extensionKey = questions
	}
}
```

Secondly we have the *object* definition. The *object* definition defines what request parameters should create a tracking information.


Therefore we have a look at our query parameter, which looks for our example like this `?tx_questions_questions[question]=1&tx_questions_questions[action]=show&cHash=fb3dd90304ba52588593187b1c8aac3d`. The important part for us is the parameter, which contains the *uid* of the record we want to track - this is `tx_questions_questions[question]=1`.

A *object* definition for our Questions/FAQ extension looks like:


```typo3_typoscript
plugin.tx_viewstatistics.settings.objects {
	// First we have main variable of the parameter
	tx_questions_questions {
		// Next the array key of the parameter
		question {
			# The label is also used for:
			# Select boxes, Table header, and more
			label = Questions/FAQ
			# The table contains the database table
			# which is related to the parameter uid
			table = tx_questions_domain_model_question
			# The title contains the database field
			# name, from which the title should be
			# read.
			title = title
		}
	}
}
```

Finally you must ensure, that this new configuration is also available for the backend module:

```typo3_typoscript
module.tx_viewstatistics < plugin.tx_viewstatistics
```


If you like to examine some more configuration examples, take a look into the *ext_typoscript_setup.txt* in the root path of the view_statistics extension.
