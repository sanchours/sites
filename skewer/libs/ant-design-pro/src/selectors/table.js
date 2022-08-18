const getModuleNameByCustomBtnName = (subList, customButtonName) => {

  const indexLib = subList.findIndex(lib => {
    return lib.name === customButtonName
  });

  if ( indexLib === -1 ){
    throw new Error('Unknown lib');
  }

  return subList[indexLib].module;

};


/**
 * Построит путь к модулю кастомной кнопки по её названию
 */
const buildCustomBtnModulePathByName = (subList, customButtonName) => {

  const indexLib = subList.findIndex(lib => {
    return lib.name === customButtonName
  });

  if ( indexLib === -1 ){
    throw new Error('Unknown lib');
  }

  //   library structure
  //   name: 'ParamsAddObjBtn', layer: 'Adm', module: 'Params', dir: '/assets/5c312e79', notOwn: false,
  const library = subList[indexLib];

  return `CustomButtonsInTableRow/${library.module}/${library.name}/Index.js`;

};


export default {
  getModuleNameByCustomBtnName,
  buildCustomBtnModulePathByName
}
