import React, { useState, useEffect } from 'react';
import { connect } from 'dva';
import { Collapse } from 'antd';
import { groupBy } from 'lodash';
import router from "umi/router";
import Scrollbars from 'react-custom-scrollbars';
import { vh, addEvent } from '../helpers/Index';
import SkList from '../skList/Index';
import styles from './style.less';
import * as treeAPI from "@/dispatches/tree";

const renderPanels = (moduleData, dispatch, onCollapse, isMobile) => {
  const { Panel } = Collapse;

  const leftPanelItemId = moduleData.get('lastActiveItemId') || 0;

  const dataSource = moduleData.getIn(['params', 'items'])
    ? moduleData.getIn(['params', 'items']).toJS() : {};

  const groups = groupBy(dataSource, 'group');

  const handleClickListItem = (itemId, selectedItemId) => () => {
    if (itemId !== selectedItemId) {
      router.push({
        pathname: `out.left.tools=${itemId}`
      });
    } else {
      treeAPI.dispatchLoadTabs(dispatch, itemId, 'tools')();
    }

    // в мобильной версии после выбора пункта меню --> закрываем меню
    if (isMobile) {
      onCollapse(true);
    }
  };

  return Object.entries(groups).map(([index, value]) => {
    return (

      <Panel
        key={index}
        className="skleftManagment"
        header={index}
      >

        <SkList
          isMobile={isMobile}
          onCollapse={onCollapse}
          items={value}
          selectedItemId={leftPanelItemId}
          handleClick={handleClickListItem}
        />
      </Panel>
    );
  });
};

export default connect(({ skGlobal, dispatch }) => {
  return {
    dispatch: dispatch,
    moduleData: skGlobal.leftLayout.get('out.left.tools'),
  };
})(({ moduleData, dispatch, isMobile, onCollapse }) => {

  const [maxHeight, setMaxHeight] = useState(vh());

  useEffect(() => {
    addEvent(window, "resize", (event)  => {
      setMaxHeight(vh())
    });
  });

  const dataSource = moduleData.getIn(['params', 'items'])
    ? moduleData.getIn(['params', 'items']).toJS() : {};

  const groups = groupBy(dataSource, 'group');

  let leftItemsCnt = 5;
  const leftPanel = document.getElementsByClassName('sk-left-panel')[0];
  if (leftPanel) {
    leftItemsCnt = leftPanel.childElementCount;
  }

  const maxHeightScrollBar = isMobile ? maxHeight - (leftItemsCnt*44+64+2*10) : maxHeight - (leftItemsCnt*44+64+2*10+33);

  return (
    <Scrollbars
      // This will activate auto-height
      className="sktree-scrollbar"
      autoHide
      autoHideTimeout={1000}
      autoHideDuration={200}
      autoHeight
      autoHeightMin={100}
      autoHeightMax={maxHeightScrollBar}
    >
      <Collapse
        bordered={false}
        className="sk-collapse-tools"
        defaultActiveKey={Object.keys(groups)}
      >
        {renderPanels(moduleData, dispatch, onCollapse, isMobile)}
      </Collapse>
    </Scrollbars>
  );
})
