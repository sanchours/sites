import React from 'react';
import { Button } from 'antd';
import Icon from '../Icon/Index'

export default ({ text, handleClickOnButton, configButton, selectedRows }) => {

  if (!configButton || !text)
    return null;

  const isSaveBtn = configButton.state === 'save';

  const icon = !configButton.iconCls
    ? ''
    : (<span className="sk-left-btn__icon">
        <Icon alias={configButton.iconCls}/>
     </span>);

  return (
    <Button
      className="sk-left-col-button sk-main-button"
      type="primary"
      disabled={selectedRows && !(selectedRows.length) && configButton.state === 'delete'}
      htmlType={isSaveBtn ? 'submit' : 'button'}
      block
      onClick={handleClickOnButton}
    >
      {icon}
      {text}
    </Button>
  );

}
