document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('sigForm');
  if (!form) return;

  // Y'dhhar message el khata2 ta7t el champ
  function showError(field, message) {
    clearError(field);
    field.classList.add('sig-invalid');
    var span = document.createElement('span');
    span.className = 'sig-field-error';
    span.textContent = message;
    field.parentNode.appendChild(span);
  }

  // Yna7i el message el 9dim
  function clearError(field) {
    field.classList.remove('sig-invalid');
    var old = field.parentNode.querySelector('.sig-field-error');
    if (old) old.remove();
  }

  // Les règles: trajja3 message ken fama khata2, wala '' ken s7i7
  function checkTitre(f) {
    var v = f.value.trim();
    if (v === '') return 'Le titre est obligatoire.';
    if (v.length < 5) return 'Le titre doit contenir au moins 5 caractères.';
    if (v.length > 120) return 'Le titre ne doit pas dépasser 120 caractères.';
    return '';
  }

  function checkCategorie(f) {
    return f.value === '' ? 'Choisissez une catégorie.' : '';
  }

  function checkDescription(f) {
    var v = f.value.trim();
    if (v.length < 10) return 'La description doit contenir au moins 10 caractères.';
    if (v.length > 3000) return 'La description ne doit pas dépasser 3000 caractères.';
    return '';
  }

  function checkAdresse(f) {
    var v = f.value.trim();
    if (v === '') return "L'adresse est obligatoire.";
    if (v.length > 255) return "L'adresse ne doit pas dépasser 255 caractères.";
    return '';
  }

  function checkPhoto(f) {
    if (!f.files.length) return ''; // photo facultative
    var file = f.files[0];
    var types = ['image/jpeg', 'image/png', 'image/webp'];
    if (types.indexOf(file.type) === -1) return 'Utilisez une image JPG, PNG ou WebP.';
    if (file.size > 2 * 1024 * 1024) return 'La photo ne doit pas dépasser 2 Mo.';
    return '';
  }

  // Rbat kol champ bel règle mte3ou
  var rules = [
    { field: form.elements['titre'],       check: checkTitre },
    { field: form.elements['categorie'],   check: checkCategorie },
    { field: form.elements['description'], check: checkDescription },
    { field: form.elements['adresse'],     check: checkAdresse },
    { field: form.elements['photo'],       check: checkPhoto }
  ];

  // Validation fowriya
  rules.forEach(function (r) {
    if (!r.field) return;
    var evt = (r.field.type === 'file' || r.field.tagName === 'SELECT') ? 'change' : 'blur';
    r.field.addEventListener(evt, function () {
      var msg = r.check(r.field);
      if (msg) { showError(r.field, msg); } else { clearError(r.field); }
    });
  });

  // 9bal ma yet'baath el formulaire
  form.addEventListener('submit', function (e) {
    var firstInvalid = null;
    rules.forEach(function (r) {
      if (!r.field) return;
      var msg = r.check(r.field);
      if (msg) {
        showError(r.field, msg);
        if (!firstInvalid) firstInvalid = r.field;
      } else {
        clearError(r.field);
      }
    });
    if (firstInvalid) {
      e.preventDefault();
      firstInvalid.focus();
    }
  });
});