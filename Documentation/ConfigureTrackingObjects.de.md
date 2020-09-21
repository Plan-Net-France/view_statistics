# Konfigurieren Sie eigene Tracking-Objekte

Zum Konfigurieren Ihrer eigenen Tracking-Objekte benötigen Sie lediglich ein wenig Setup-TypoScript. Die
TypoScript-Definition ist in zwei Teile unterteilt, die in das Root-Template eingefügt werden müssen.

Zuerst haben wir die Definition des *Typs*. Die Definition *Typ* ist erforderlich, um dem Backend-Modul beizubringen,
dass es ein neues Tracking-Objekt gibt, es registriert z. B. einen Auswahlfeldeintrag.

Eine *Typ* -Definition für unsere Frage/FAQ-Erweiterung sieht folgendermaßen aus:

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

Zweitens haben wir die *Objekt* -Definition. Die *Objekt* -Definition definiert, welche Anforderungsparameter
Trackinginformationen erstellen sollen.


Daher sehen wir uns unseren Abfrageparameter an, der für unser Beispiel wie folgt aussieht:

`?tx_questions_questions[question]=1&tx_questions_questions[action]=show&cHash=fb3dd90304ba52588593187b1c8aac3d`

Der wichtige Teil für uns ist der Parameter, der die *uid* des Datensatzes enthält, den wir verfolgen möchten - dies
ist `tx_questions_questions [question] = 1`.

Eine *Objekt* -Definition für unsere Frage/FAQ-Erweiterung sieht folgendermaßen aus:

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

Schließlich müssen Sie sicherstellen, dass diese neue Konfiguration auch für das Backend-Modul verfügbar ist:

```typo3_typoscript
module.tx_viewstatistics < plugin.tx_viewstatistics
```

Wenn Sie weitere Konfigurationsbeispiele untersuchen möchten, sehen Sie sich die Datei
*ext_typoscript_setup.txt* im Rootpfad der Erweiterung view_statistics an.
