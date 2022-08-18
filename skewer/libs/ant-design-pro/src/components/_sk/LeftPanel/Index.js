import React from 'react';
import { Collapse } from 'antd';
import router from 'umi/router'
import { connect } from "dva";
import TreeSection from '../TreeSection/Index';
import TreeTemplates from '../TreeTemplates/Index';
import TreeLibs from '../TreeLibs/Index';
import CatalogList from '../CatalogList/Index';
import ToolPanel from '../ToolPanel/Index';
import style from './style.less';
import * as UilIcons from '@iconscout/react-unicons/';


const Index = ({ sidebarActiveItem, leftLayout, collapsed, isMobile, onCollapse }) => {

  const activeKey = (!collapsed) ? sidebarActiveItem : null;

  const buildPanels = () => {

    const { Panel } = Collapse;

    const panels = [];

    Object.entries(leftLayout.toJS()).forEach(([path, value]) => {

      const keyPanel = path.substring(path.lastIndexOf('.') + 1);

      let content = null;
      let icon = null;

      switch (keyPanel) {
        case 'section':
          content = <TreeSection isMobile={isMobile} onCollapse={onCollapse} />;
          icon = <UilIcons.UilListUl size="24" />;
          break;
        case 'tpl':
          content = <TreeTemplates isMobile={isMobile} onCollapse={onCollapse} />;
          icon = <UilIcons.UilWindowGrid size="24" />;
          break;
        case 'lib':
          content = <TreeLibs isMobile={isMobile} onCollapse={onCollapse} />;
          icon = <UilIcons.UilBookOpen size="24" />;
          break;
        case 'catalog':
          content = <CatalogList isMobile={isMobile} onCollapse={onCollapse} />;
          icon = <UilIcons.UilApps size="24" />;
          break;
        case 'tools':
          content = <ToolPanel isMobile={isMobile} onCollapse={onCollapse} />;
          icon = <UilIcons.UilSlidersVAlt size="24" />;
          break;
        default:
      }

      const header = () => {
        return (
          <React.Fragment>
            <span className="sk-left-panel__header-icon">{icon}</span>
            <span title={leftLayout.getIn([path, 'init', 'title'])} className="sk-header-content">{leftLayout.getIn([path, 'init', 'title'])}</span>
          </React.Fragment>
        )
      };

      if (content) {
        panels.push(
          <Panel
            className={`sidebar-header sidebar-header-${keyPanel}`}
            header={header()}
            key={keyPanel}
          >
            <div className="sk-panel-content">
              {content}
            </div>
          </Panel>
        );
      }

    });

    return panels;

  };


  return (
    <Collapse
      className="sk-left-panel"
      accordion
      expandIconPosition="right"
      activeKey={activeKey}
      onChange={(newKey) => {

        if (newKey) {
          const lastActiveItemId = leftLayout.getIn([
            `out.left.${newKey}`, 'lastActiveItemId'
          ], 0);
          router.push({
            pathname: `/out.left.${newKey}=${lastActiveItemId}`
          });
        }

      }}
    >
      {buildPanels()}
    </Collapse>
  );
};

export default connect(({ skGlobal }) => {
  return {
    leftLayout: skGlobal.leftLayout,
    sidebarActiveItem: skGlobal.sidebarActiveItem
  }
})(Index);
