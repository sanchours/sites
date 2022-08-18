import React, {useEffect} from 'react';
import { Spin, Tabs } from 'antd';
import { connect } from 'dva';
import classNames from 'classnames';
import router from 'umi/router';
import TestForm from '../TestForm/Index';
import TestTable from '../TestTable/Index';
import FileBrowserFiles from '../FileBrowserFiles/Index'
import Files from '../Files/Index';
import FileAddForm from '../FileAddForm/Index'
import styles from './Index.less'
import * as treeAPI from "@/dispatches/tree";
import * as sk from "@/services/_sk/api";

const TabsComponent = ({ routerParams, tabs, activeTab, dispatch, loading }) => {

  const {leftPanelItemName, leftPanelItemId, tabsItemName} = routerParams;

  useEffect(() => {
    if ( leftPanelItemId && (leftPanelItemId !== '0') && leftPanelItemName ){
      treeAPI.dispatchLoadTabs(dispatch, leftPanelItemId, leftPanelItemName)();
    }
  }, [leftPanelItemId, leftPanelItemName]);

  // При обновлении имени таба в урле запрашиваем данные для этого таба
  useEffect(() => {
    if (tabsItemName){
      dispatch({
        type: 'skGlobal/fetchOneTab',
        payload: { activeKey: `out.tabs.${tabsItemName}` },
      });
    }
  }, [tabsItemName]);

  if (!tabs.size) {
    return '';
  }

  const buildContentOneTab = key => {

    if (!tabs.size) return '';

    const contentData = tabs.get(key);
    const extComponent = contentData.getIn(['params', 'extComponent']);
    const serviceData = contentData.getIn(['params', 'serviceData']);
    const moduleLayer = contentData.get('moduleLayer', null);
    const moduleName = contentData.get('moduleName', null);
    const url = serviceData.get('url', null);

    let MainReactComponent = '';

    if ( contentData.size ){

      if ( (!extComponent || extComponent === 'Form') ) {

        if ( contentData.getIn(['moduleName']) === 'Files' ){

          switch ( contentData.getIn(['params', 'componentName']) ){
            case 'FileBrowserImages':
              MainReactComponent = <Files tabKey={key} moduleData={contentData} />;
              break;
            case 'FileAddForm':
              MainReactComponent = <FileAddForm tabKey={key} moduleData={contentData} />;
              break;
            case 'FileBrowserFiles':
              MainReactComponent = <FileBrowserFiles tabKey={key} moduleData={contentData} />;
              break;
            default:

          }

        } else if (contentData.getIn(['params', 'items']))
          MainReactComponent = <TestForm tabKey={key} moduleData={contentData} />;
        else {
          MainReactComponent = '';
        }
      } else if (extComponent === 'Iframe' && url){
          const iframeUrl = (moduleLayer && moduleName)
            ? `${url}${(/\?/.test(url) ? '&' : '?')}moduleName=${moduleLayer}_${moduleName}`
            : url;

          MainReactComponent = <iframe src={window.location.origin + iframeUrl} style={{width: '100%', height: 'calc(100vh - 160px)'}} />
      } else {
        MainReactComponent = <TestTable tabKey={key} moduleData={contentData} />;
      }

    }

    return (
      <Spin spinning={loading} size={'large'}>
        {MainReactComponent}
      </Spin>
    );
  };

  const handleChangeTab = activeKey => {
    const newTab = activeKey.substring(activeKey.lastIndexOf('.') + 1);
    router.push({
      pathname: `/out.left.${leftPanelItemName}=${leftPanelItemId};out.tabs=${newTab}`
    });
  };

  // Активная вкладка
  const activeKey = `out.tabs.${activeTab}`;

  return (
    <Tabs
      className={classNames(styles.skTabs, {"sk-tabs": true})}
      onChange={handleChangeTab}
      type="card"
      activeKey={activeKey}
    >
      {
        tabs.valueSeq().map(val => {
          return (
            <Tabs.TabPane
              key={val.getIn(['path'])}
              tab={val.getIn(['params', 'componentTitle'])}
            >
              {buildContentOneTab(val.getIn(['path']))}
            </Tabs.TabPane>
          );
        })
      }
    </Tabs>
  );
};

export default connect(({ skGlobal, loading }) => {
  return {
    tabs: skGlobal.tabs,
    activeTab: skGlobal.activeTab,
    routerParams: skGlobal.routerParams,
    loading:
      !!loading.effects['skGlobal/postData4Tabs'] ||
      !!loading.effects['skGlobal/loadTabs'] ||
      !!loading.effects['skGlobal/loadTabsWithoutModal'] ||
      !!loading.effects['skGlobal/sortTableItems']
  };
})(TabsComponent)
