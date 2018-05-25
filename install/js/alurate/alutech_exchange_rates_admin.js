function addProductsListRow(mess1, mess2, id)
{
  var table = document.getElementById("tableProductsList");
  var tablerows = table.rows.length;
  var key = id + 1;
  var tableId = 'product_group_'+key;
  var row = table.insertRow(tablerows);
  var cell1 = row.insertCell(0);
  var newProduct = '<table style="width: 100%;" id="' + tableId + '">' +
    '<tr class="heading">' +
    '<td align="center">' +
    'Направление продукции: ' +
    '<input size="80" maxlength="255" name="EXRT_PROPERTY_' +
    key +
    '_PRODUCTS_GROUP" id="EXRT_PROPERTY_' +
    key +
    '_PRODUCTS_GROUP" value="" type="text">' +
    '</td>' +
    '</tr>' +
    '</table>' +
    '<div style="width: 100%; text-align: center; margin: 10px 0;">' +
    '<input class="adm-btn-big" onclick="addGroupListRow(mess3, mess4, GroupsId, ' + "'" + tableId + "'" + ');" type="button" value="'+ mess1 + '" title="' + mess2 + '">' +
    '</div>';
  cell1.style.border = "1px solid #d0d7d8";
  cell1.innerHTML = newProduct;
  ProductsId = key;
}

function addGroupListRow(mess1, mess2, id, tableId)
{
  var table = document.getElementById(tableId);
  var tablerows = table.rows.length;
  var key = id + 1;
  var tableIdNew = 'group_name_'+key;
  var row = table.insertRow(tablerows);
  var cell1 = row.insertCell(0);
  var newSeries = '<tr>' +
    '<td>' +
    '<table class="internal" style="margin: 0 auto; width: 100%;" id="' + tableIdNew + '">' +
    '<colgroup><col width="5%"><col width="45%"><col width="15%"><col width="15%"><col width="15%"><col width="5%"></colgroup>' +
    '<tr class="heading">' +
    '<td colspan="6" align="center">' +
    'Серия автоматики: ' +
    '<input size="80" maxlength="255" name="EXRT_PROPERTY_' +
    key +
    '_GROUP_NAME" id="EXRT_PROPERTY_' +
    key +
    '_GROUP_NAME" value="" type="text">' +
    '</td>' +
    '</tr>' +
    '</table>' +
    '' +
    '<div style="width: 100%; text-align: center; margin: 10px 0;">' +
    '<input class="adm-btn-big" onclick="addItemsListRow(ItemsId, ' + "'" + tableIdNew + "'" + ');" type="button" value="' +
    mess1+
    '" title="' +
    mess2 +
    '">' +
    '</div>' +
    '</td>' +
    '</tr>';
  cell1.innerHTML = newSeries;
  GroupsId = key;
}

function addItemsListRow(id, tableId)
{
  var table = document.getElementById(tableId);
  var tablerows = table.rows.length;
  var key = id + 1;
  var row = table.insertRow(tablerows);
  var cell1 = row.insertCell(0);
  var newItems = '<tr id="EXRT_PROPERTY_' + key + '">' +
    '<td style="vertical-align:middle;">' + key + '</td>' +
    '<td><input size="50" maxlength="255" name="EXRT_PROPERTY_' + key + '_NAME" id="EXRT_PROPERTY_' + key + '_NAME" value="" type="text" onchange="translitName(this, ' + "'EXRT_PROPERTY_" + key + "_CODE'" + ')"></td>' +
    '<td>EURO: <input size="7" maxlength="30" name="EXRT_PROPERTY_' + key + '_EUR_RATE" id="EXRT_PROPERTY_' + key + '_EUR_RATE" value="" type="text" oninput="convertEuro(this.value, ExchRatRes, ' + "'EXRT_PROPERTY_" + key + "_BYN_RATE'" + ')"></td>' +
    '<td>BYN: <input size="7" maxlength="30" name="EXRT_PROPERTY_' + key + '_BYN_RATE" id="EXRT_PROPERTY_' + key + '_BYN_RATE" value="" type="text"></td>' +
    '<td><input size="20" maxlength="50" name="EXRT_PROPERTY_' + key + '_CODE" id="EXRT_PROPERTY_' + key + '_CODE" value="" type="text" onchange="errMessToCode(this.value, ' + "'EXRT_PROPERTY_" + key + "_NAME'" + ', ' + "'EXRT_PROPERTY_" + key + "_CODE'" + ')"></td>' +
    '<td style="text-align: center; vertical-align:middle;">' +
    '<input name="EXRT_PROPERTY_' + key + '_DEL" id="EXRT_PROPERTY_' + key + '_DEL" value="" class="adm-designed-checkbox" type="checkbox">' +
    '<label class="adm-designed-checkbox-label" for="EXRT_PROPERTY_' + key + '_DEL" title=""></label>' +
    '</td>' +
    '</tr>';
  row.innerHTML = newItems;
  ItemsId = key;
}

function translitName(res, id)
{
  var input = document.getElementById(id);
  var str = res.value;
  var _letterAssociations = {
    "а": "a", "б": "b", "в": "v", "ґ": "g", "г": "g", "д": "d",
    "е": "e", "ё": "e", "є": "ye", "ж": "zh", "з": "z", "и": "i",
    "і": "i", "ї": "yi", "й": "i", "к": "k", "л": "l", "м": "m",
    "н": "n", "о": "o", "п": "p", "р": "r", "с": "s", "т": "t",
    "у": "u", "ф": "f", "х": "h", "ц": "c", "ч": "ch", "ш": "sh",
    "щ": "sh'", "ъ": "", "ы": "i", "ь": "", "э": "e", "ю": "yu",
    "я": "ya",
    ' ': '-', '_': '_', '`': '_', '~': '_', '!': '_', '@': '_',
    '#': '_', '$': '_', '%': '_', '^': '_', '&': '_', '*': '_',
    '(': '', ')': '', '-': '-', '\=': '_', '+': '_', '[': '',
    ']': '', '\\': '_', '|': '_', '/': '_','.': '_', ',': '_',
    '{': '', '}': '', '\'': '_', '"': '_', ';': '_', ':': '_',
    '?': '_', '<': '', '>': '', '№':'_'
  };

  if (!str) {
    return "";
  }

  var new_str = "";
  for (i = 0; i < str.length; i++) {
    var strLowerCase = str[i].toLowerCase();

    var new_letter = _letterAssociations[strLowerCase];
    if ("undefined" === typeof new_letter) {
      new_str += strLowerCase;
    }
    else {
      new_str += new_letter;
    }
  }

  errMessToCode(new_str, res.id, id);
}

function convertEuro(euro, exchangeRate, id)
{
  if (!euro && !exchangeRate) {
    return "";
  }

  var byn = Math.round( euro * exchangeRate );

  document.getElementById(id).value = byn;

  //console.log();
}

function errMessToCode(code, nameId, codeId)
{
  var inputName = document.getElementById(nameId);
  var inputCode = document.getElementById(codeId);
  var save = document.querySelector('[name="save"]');
  var apply = document.querySelector('[name="apply"]');

  if( find(code) === -1 )
  {
    if(inputName.style.borderColor === "red")
      inputName.style.borderColor = "#87919c #959ea9 #9ea7b1 #959ea9";

    if(inputCode.style.borderColor === "red")
      inputCode.style.borderColor = "#87919c #959ea9 #9ea7b1 #959ea9";
    inputCode.value = code;

    if(save.disabled == true)
      save.disabled = false;
    if(apply.disabled == true)
      apply.disabled = false;

    ItemsCode.push(code);
  }
  else
  {
    save.disabled = true;
    apply.disabled = true;

    inputName.style.borderColor = "red";

    inputCode.style.borderColor = "red";
    inputCode.value = "Такой код уже существует";

  }

}

function find(value) {

  //console.log(ItemsCode);

  if (ItemsCode.indexOf) { // если метод существует
    return ItemsCode.indexOf(value);
  }

  for (var i = 0; i < ItemsCode.length; i++) {
    if (ItemsCode[i] === value) return i;
  }

  return -1;
}