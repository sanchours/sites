import React from 'react'
import { Icon } from 'antd';
import styles from './style.less';

export default ({ onClick, collapsed }) => {
  return (
    <div
      onClick={onClick}
      className="sk-menu-collapser"
    >
      <Icon type={collapsed ? "menu-fold" : "menu-unfold"} style={{ fontSize: "20px" }} />
    </div>
  )
}
