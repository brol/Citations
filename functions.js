// paramètres par défaut du formulaire de paramétrage
function pdefaults()
{
	document.state.active.checked = false;
}

// action d'édition depuis la liste
function ledit(id)
{
	document.listblog.op.value = 'edit';
	document.listblog.id.value = id;	
	document.listblog.submit();
}

// import des données
function pimport()
{
	document.impexp.op.value = 'import';
	document.impexp.submit();
}