import React from 'react';
import {Button, Divider, Dropdown, Switch} from "antd";
import stylesMain from "../../../layouts/_sk/MainLayout.less";
import styles from "./style.less";
import UilUser from '@iconscout/react-unicons/icons/uil-user';
import UilUserExclamation from '@iconscout/react-unicons/icons/uil-user-exclamation';

export default ({moduleData, className, dispatch}) => {

  const handleClickOnLogout = () => {
    dispatch({
      type: 'skGlobal/logout',
      payload: {
        path: 'out.header.auth',
      }
    });
  };

  if (!moduleData)
    return null;

  const langValues = moduleData.getIn(['init', 'lang']);

  const renderData = moduleData.getIn(['init', 'renderData']);

  const onChangeAdmSwitcher = checked => {
    dispatch({
      type: 'skGlobal/setAdminMode',
      payload: {
        path: 'out.header.auth',
        mode: checked
      }
    });
  };

  const onChangeCacheSwitcher = () => {
    dispatch({
      type: 'skGlobal/setCacheMode',
      payload: {
        path: 'out.header.auth'
      }
    });
  };

  const onChangeDebugSwitcher = () => {
    dispatch({
      type: 'skGlobal/setDebugMode',
      payload: {
        path: 'out.header.auth'
      }
    });
  };

  const onChangeCompressionSwitcher = () => {
    dispatch({
      type: 'skGlobal/setCompressionMode',
      payload: {
        path: 'out.header.auth'
      }
    });
  };

  // console.log( moduleData.toJS() );

  return (
    <Dropdown
      className={className}
      overlay={
        <div className="nav-top_user-dd">
          <div>
            <p>{renderData.get('username')}</p>
            <p>{langValues.get('authLastVisit')}: {renderData.get('lastlogin')}</p>
            {renderData.get('showAdmSwitcher') &&
              <>
                <p>
                  <Switch
                    className={styles.switch}
                    defaultChecked={renderData.get('admSwitcherVal')}
                    onChange={onChangeAdmSwitcher}
                  />
                  admin mode
                </p>
                <p>
                  <Switch
                    className={styles.switch}
                    defaultChecked={renderData.get('changeCacheMode')}
                    onChange={onChangeCacheSwitcher}
                  />
                   {renderData.get('changeCacheMode') ? langValues.get('cache_flag_off') : langValues.get('cache_flag_on')}
                </p>
                <p>
                  <Switch
                    className={styles.switch}
                    defaultChecked={renderData.get('changeDebugMode')}
                    onChange={onChangeDebugSwitcher}
                  />
                  {renderData.get('changeDebugMode') ? langValues.get('debug_flag_off') : langValues.get('debug_flag_on')}
                </p>
                <p>
                  <Switch
                    className={styles.switch}
                    defaultChecked={renderData.get('compression')}
                    onChange={onChangeCompressionSwitcher}
                  />
                  {renderData.get('compression') ? langValues.get('compression_flag_off') : langValues.get('compression_flag_on')}
                </p>
              </>
            }
          </div>
          <Divider />
          <Button
            className="sk-main-button"
            type='primary'
            onClick={handleClickOnLogout}
          >
            {langValues.get('authLogoutButton')}
          </Button>
        </div>
      }
    >
      <span className={`${stylesMain.action}`}>
        {
          (renderData.get('showAdmSwitcher') &&
            (renderData.get('admSwitcherVal') ||
              renderData.get('changeCacheMode') ||
              renderData.get('changeDebugMode') ||
              !renderData.get('compression'))) ?
            <UilUserExclamation size="20" color="#E9535C" className="unicons unicons-globe" /> :
            <UilUser type="user" size="20" color="#F4AB3D" className="unicons unicons-globe" />
        }
      </span>
    </Dropdown>
  );

}
