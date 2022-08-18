import React from 'react';
import { Drawer } from 'antd';
import SiderMenu from './Index';
import styles from "@/components/_sk/SiderMenu/index.less";
import Link from "umi/link";
import CanapeLogo from "../CanapeLogo/Index";
import LeftPanel from "../LeftPanel/Index";

const SiderMenuWrapper = React.memo(props => {
  const { isMobile, collapsed, onCollapse } = props;

  const Logo = () => {
    return (
      <div className={styles.logo} id="logo">
        <Link to="/">
          <CanapeLogo inverse />
        </Link>
      </div>
    )
  };

  return isMobile ? (
    <Drawer
      visible={!collapsed}
      placement="left"
      onClose={() => onCollapse(true)}
      title={<Logo />}
      width={320}
      style={{
        padding: 0,
        height: '100vh',
      }}
    >
      <LeftPanel
        collapsed={collapsed}
        isMobile={isMobile}
        onCollapse={onCollapse}
      />
    </Drawer>
  ) : (
    <SiderMenu {...props} />
  );
});

export default SiderMenuWrapper;
