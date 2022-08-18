import React from 'react';
import {Dropdown, Menu} from 'antd';
import classNames from "classnames";
import UilPalette from "@iconscout/react-unicons/icons/uil-palette";
import styles from './Index.less';

export default ({moduleData, className, dispatch}) => {
  if (!moduleData) {
    return null;
  }
  const options = moduleData.getIn(['init', 'SelectDesign']);

  const links = [];
  const options_array = options.toArray();
  for (const k in options_array) {
    if (options_array.hasOwnProperty(k)) {
      const element = options_array[k];
      const link_data = element.toObject();
      links.push(<Menu.Item key={link_data.href}>
        <a href={link_data.href} target="_blank">{link_data.title}</a>
      </Menu.Item>);
    }
  }

  const langMenu = (
    <Menu
      className={styles.menu}
    >
      {links}
    </Menu>
  );

  return (
    <Dropdown overlay={langMenu} placement="bottomRight">
      <span className="nav-top_design">
        <span className={classNames(styles.dropDown, className)}>
          <UilPalette color="#F82525" size="22" className="unicons unicons-palette" />
        </span>
      </span>
    </Dropdown>
  );
}
