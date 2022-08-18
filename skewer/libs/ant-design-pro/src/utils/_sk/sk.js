/**
 * Устанавливает значение в поле формы по заданному пути
 * @param path путь до файла (example: out.left.section)
 * @param fieldName имя поля для запрлнения
 * @param fieldValue новое значение
 */
export function setField(path, fieldName, fieldValue) {

  window.g_app._store.dispatch({
    type: 'skGlobal/updateForm2Param',
    payload: {
      path,
      fieldName,
      fieldValue,
    }
  });

}

export function getSectionId() {
  return window.g_app._store.getState().skGlobal.routerParams.leftPanelItemId;
}

export function gerUrlParams() {
  let searchObject = {}, queries, split, i;
  queries = window.location.search
    .replace(/^\?/, '')
    .split('&');

  if (queries) {
    for (i = 0; i < queries.length; i++) {
      split = queries[i].split('=');
      if (split[0] && split[1] !== undefined) {
        searchObject[split[0]] = split[1];
      }
    }
  }
  return searchObject;
}

export function getUrlParam(param, defaultvalue = undefined) {
  const urlParams = gerUrlParams();

  return (urlParams && urlParams[param] !== undefined)
    ? urlParams[param]
    : defaultvalue;
}


