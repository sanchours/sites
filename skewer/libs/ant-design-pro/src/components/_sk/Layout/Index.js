import React, {useEffect, useState} from 'react';
import { Layout } from "antd";
import logo from "../../../assets/logo.svg";
import styles from "../../../layouts/_sk/MainLayout.less";
import SiderMenuDrawer from '../SiderMenu/SiderMenuDrawer';
import Header from '../Header/Index';
import Footer from '../../../layouts/_sk/Footer'
import SiderCollapseButton from '../SiderCollapseButton/Index'

export default (props) => {

  const {dispatch, navTheme, layout: PropsLayout, children, isMobile, fixedHeader, moduleData} = props;

  const [collapsed, setCollapsed] = useState(true);

  const handleMenuCollapse = collapsed => {
    setCollapsed(collapsed);
    dispatch({
      type: 'skGlobal/changeLayoutCollapsed',
      payload: collapsed,
    });
  };

  const handleClick = () => {
    setCollapsed(!collapsed);
  };

  const isTop = PropsLayout === 'topmenu';
  const contentStyle = !fixedHeader ? { paddingTop: 0 } : {};

  return (
    <Layout hasSider={false}>
      <Layout hasSider>
        {isTop && !isMobile ? null : (
          <SiderMenuDrawer
            logo={logo}
            theme={navTheme}
            collapsed={collapsed}
            onCollapse={handleMenuCollapse}
            isMobile={isMobile}
            {...props}
          />
        )}
        <Layout
          style={{
            minHeight: '100vh',
          }}
        >
          <SiderCollapseButton
            onClick={handleClick}
            collapsed={collapsed}
          />
          <Header />

          <Layout.Content className={styles.content} style={contentStyle}>
            {children}
          </Layout.Content>
        </Layout>

      </Layout>

      <Footer isMobile={isMobile} />

    </Layout>
  );

};
