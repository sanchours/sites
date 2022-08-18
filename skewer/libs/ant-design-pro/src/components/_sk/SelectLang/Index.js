import React from 'react';
import {Menu, Icon, Dropdown} from 'antd';
import classNames from 'classnames';
import styles from './Index.less';
import UilGlobe from '@iconscout/react-unicons/icons/uil-globe';

export default ({ moduleData, className, dispatch }) => {

  const changeLang = ({ key }) => {
    dispatch({
      type: 'skGlobal/setLang',
      payload: {
        path: 'out.header.lang',
        lang: key
      }
    });
  };

  if (!moduleData)
    return null;

  const currentLang = moduleData.getIn(['init', 'currentLang']);

  const langMenu = (
    <Menu
      className={styles.menu}
      selectedKeys={[ currentLang ]}
      onClick={changeLang}
    >
      {
        moduleData.getIn(['init', 'langList']).toJS().map( val => {
          return (
            <Menu.Item key={val.name}>
              {val.title}
            </Menu.Item>
          );
        })
      }
    </Menu>
  );

  return (
    <Dropdown
      className="nav-top_lang"
      overlay={langMenu}
      placement="bottomRight"
    >
      <span className={classNames(styles.dropDown, className)}>
        <UilGlobe size="20" className="unicons unicons-globe" type="global" />
      </span>
    </Dropdown>
  );

}
