import React from 'react';
import { connect } from 'dva';
import router from "umi/router";
import SkList from '../skList/Index';
import * as treeAPI from "@/dispatches/tree";

const CatalogList = ({ moduleData, dispatch, onCollapse, isMobile }) => {
  const handleClickListItem = (itemId, selectedItemId) => () => {
    if (itemId !== selectedItemId) {
      router.push({
        pathname: `out.left.catalog=${itemId}`
      });
    } else {
      treeAPI.dispatchLoadTabs(dispatch, itemId, 'catalog')();
    }

    // в мобильной версии после выбора пункта меню --> закрываем меню
    if (isMobile) {
      onCollapse(true);
    }
  };

  const leftPanelItemId = moduleData.get('lastActiveItemId') || 0;

  const dataSource = moduleData.getIn(['params', 'items']).toJS();

  return (
    <SkList
      items={dataSource}
      selectedItemId={leftPanelItemId}
      handleClick={handleClickListItem}
    />
  );
};

export default connect(({ skGlobal, dispatch }) => {
  return {
    dispatch: dispatch,
    moduleData: skGlobal.leftLayout.get('out.left.catalog'),
  };
})(CatalogList)
