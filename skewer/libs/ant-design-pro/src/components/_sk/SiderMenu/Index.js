import React, { PureComponent, Suspense } from 'react';
import { Layout } from 'antd';
import classNames from 'classnames';
import Link from 'umi/link';
import styles from './index.less';
import PageLoading from '../../PageLoading';
import LeftPanel from '../LeftPanel/Index';
import CanapeLogo from '../CanapeLogo/Index'

const { Sider } = Layout;

let firstMount = true;

export default class SiderMenu extends PureComponent {
  componentDidMount() {
    firstMount = false;
  }

  render() {
    const { collapsed, onCollapse, fixSiderbar, theme, isMobile } = this.props;

    const siderClassName = classNames(styles.sider, {
      [styles.fixSiderBar]: fixSiderbar,
      [styles.light]: theme === 'light',
    });
    return (
      <Sider
        trigger={null}
        collapsible
        collapsed={collapsed}
        breakpoint="lg"
        onCollapse={collapse => {
          if (firstMount || !isMobile) {
            onCollapse(collapse);
          }
        }}
        width={310}
        theme={theme}
        className={siderClassName}
        style={{display: collapsed ? 'none' : 'block'}}
      >
        <div className={styles.logo} id="logo">
          <Link to="/">
            <CanapeLogo inverse />
          </Link>
        </div>
        <Suspense fallback={<PageLoading />}>
          <LeftPanel isMobile={isMobile} collapsed={collapsed} />
        </Suspense>
      </Sider>
    );
  }
}
