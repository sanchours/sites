import React from 'react';
import {Layout} from "antd";
import {connect} from "dva";
import SelectLang from "../SelectLang/Index"
import SelectDesign from "../SelectDesign/Index"
import Profile from '../Profile/Index'
import Search from '../Search/Index';
import Cache from '../Cache/Index'
import Help from '../Help/Index'
import styles from './Index.less'

const Header = ({headerLayoutData, dispatch, moduleData}) => {
  return (
    <Layout.Header style={{ padding: 0 }}>
      <div className={styles.header}>

        <Search
          moduleData={headerLayoutData.get('out.header.search')}
          dispatch={dispatch}
        />

        <div className={styles.right}>
          <Cache
            moduleData={headerLayoutData.get('out.header.cache')}
            dispatch={dispatch}
          />

          <SelectLang
            className={styles.action}
            moduleData={headerLayoutData.get('out.header.lang')}
            dispatch={dispatch}
          />

          <SelectDesign
            className={styles.action}
            moduleData={moduleData}
            dispatch={dispatch}
          />

          <Help
            moduleData={moduleData}
          />

          <Profile
            className={styles.action}
            moduleData={headerLayoutData.get('out.header.auth')}
            dispatch={dispatch}
          />
        </div>

      </div>
    </Layout.Header>
  );
};


export default connect(({ skGlobal }) => {
  return {
    headerLayoutData: skGlobal.headerLayout,
    moduleData: skGlobal.headerModuleLayout.get('out.header')
  };
})(Header)
