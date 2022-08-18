export  function parseRouterParams(sectionName, tabName){

  let tabsItemName = null;
  let leftPanelItemName = null;
  let leftPanelItemId = null;

  if (sectionName){
    const regex4SectionName = /out\.left\.([^=]*)=([^/]*)/gm;
    const result4SectionName = regex4SectionName.exec(sectionName);
    leftPanelItemName = result4SectionName[1];
    leftPanelItemId = result4SectionName[2];
  }

  if (tabName){
    const regex4TabName = /out\.tabs=([^/]*)/gm;
    const result4TabName = regex4TabName.exec(tabName);
    tabsItemName = result4TabName[1];
  }

  return {
    tabsItemName,
    leftPanelItemName,
    leftPanelItemId
  }

}
