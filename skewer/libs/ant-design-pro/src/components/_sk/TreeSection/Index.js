import React from 'react';
import { Button, Col } from 'antd';
import { connect } from 'dva';
import router from "umi/router";
import SkTree from '../skTree/Index';
import styles from './style.less'

const TreeSection = ({ dispatch, moduleData, isMobile, onCollapse }) => {

  const handleClickSiteSettings = () => {
    router.push({
      pathname: '/out.left.section=3'
    });
  };

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
      moduleName="section"
      openNodes={getOpenNodes()}
      moduleData={moduleData}
      dispatch={dispatch}
      typeNewPage={null}
      addButtons={(
        <Col span={12}>
          <Button
            type="primary"
            htmlType="button"
            className="sk-main-button--grey"
            block
            size="small"
            onClick={handleClickSiteSettings}
          >
            {moduleData.getIn(['init','lang','siteSettings'])}
          </Button>
        </Col>
      )}
    />
  );

};

export default connect(({ skGlobal }) => {
  return {
    moduleData: skGlobal.leftLayout.get('out.left.section'),
  };
})(TreeSection);
