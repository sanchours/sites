import React from 'react';
import {Helmet} from "react-helmet";
import { connect } from 'dva';
import Tabs from '../../../components/_sk/Tabs/Index';
import styles from './style.less';

const Index = ({ routerParams }) => {

  const {leftPanelItemName, leftPanelItemId} = routerParams;

  return (
    <>
      <Helmet>
        <title>{window.titlePage || 'Canape CMS'}</title>
      </Helmet>
      <div className={styles.MainContainer}>
        {!leftPanelItemName && !leftPanelItemId ? (
          <div style={{ padding: '10px 20px' }}>
            <h2>Canape CMS 4</h2>
          </div>
        ) : (
          <Tabs />
        )}
      </div>
    </>
  );

};

export default connect(({ skGlobal, loading }) => {
  return {
    routerParams: skGlobal.routerParams,
  };
})(Index)
