import React from 'react';
import { connect } from 'dva';
import SkTree from '../skTree/Index';

const TreeTemplates = ({ dispatch, moduleData, isMobile, onCollapse }) => {

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
      leftPanelItemId={leftPanelItemId}
      moduleName="tpl"
      openNodes={getOpenNodes()}
      moduleData={moduleData}
      dispatch={dispatch}
      typeNewPage={null}
    />
  );

};

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.leftLayout.get('out.left.tpl'),
  };
})(TreeTemplates)

