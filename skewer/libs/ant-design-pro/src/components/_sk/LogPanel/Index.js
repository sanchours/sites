import React, {useState} from 'react';
import classNames from 'classnames';
import {connect} from "dva";
import { Icon, Badge } from 'antd';
import styles from './Index.less';

const LogPanel = ({errors, moduleData, dispatch}) => {

  const langLabel = moduleData.getIn(['init', 'lang']).toJS();

  const [collapsed, setCollapsed] = useState(true);

  const toggleCollapse = () => {
    setCollapsed(!collapsed);
  };

  const clearErrors = e => {
    e.stopPropagation();
    dispatch({
      type: 'skGlobal/clearErrors',
      payload: {}
    });
  };

  const renderToolBar = () => {
    return !collapsed ? (
      <div className="logs-panel__toolbar">
        <Icon type="arrow-down" />
        <Icon type="delete" onClick={clearErrors} />
      </div>
    ) : null;
  };

  return (
    <div className={classNames('logs-panel', { 'logs-panel--collapsed': collapsed })}>
      <div className="logs-panel__title" onClick={toggleCollapse}>
        {langLabel.logPanelHeader} <Badge count={errors.length} />
        {renderToolBar()}
      </div>
      <div className="logs-panel__content">
        <ul>
          {errors.map((val, index) => {
            const keyItem = `key_log_panel_${index}`;
            return <li key={keyItem}><span dangerouslySetInnerHTML={{ __html: val }} /></li>
          })}
        </ul>
      </div>
    </div>
  );
};

export default connect(({skGlobal}) => {
  return {
    errors: skGlobal.errors,
    moduleData: skGlobal.logLayout.get('out.log')
  };
})(LogPanel)
