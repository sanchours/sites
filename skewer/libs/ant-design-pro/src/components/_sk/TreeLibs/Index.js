import React from 'react';
import { connect } from 'dva';
import SkTree from '../skTree/Index';

const TreeLibs = ({ dispatch, moduleData, isMobile, onCollapse }) => {

  const getOpenNodes = () => {

    const parents = moduleData && moduleData.getIn(['params', 'parents'])
      ? moduleData.getIn(['params', 'parents']).toJS()
      : [];

    return parents.map(val => val.toString());
  };

  const leftPanelItemId = moduleData.get('lastActiveItemId') || 0;

  return (
    <SkTree
      isMobile={isMobile}
      onCollapse={onCollapse}
      moduleName="lib"
      moduleData={moduleData}
      leftPanelItemId={leftPanelItemId}
      openNodes={getOpenNodes()}
      dispatch={dispatch}
      typeNewPage={1}
    />
  );
};

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.leftLayout.get('out.left.lib'),
  };
})(TreeLibs)
