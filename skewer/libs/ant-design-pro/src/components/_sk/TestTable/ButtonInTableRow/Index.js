import React from 'react';
import {Tooltip} from "antd";
import Icon from '../../Icon/Index'

export default ({ configButton, handleClickOnButton } ) => {

  const handleClick = (e) => {
    e.stopPropagation();
    handleClickOnButton();
  };

  if ( !configButton )
    return null;

  return (
    <Tooltip
      placement="bottomRight"
      title={configButton.tooltip}
    >
      <span>
        <Icon
          alias={configButton.iconCls}
          addProps={{onClick: handleClick}}
        />
      </span>
    </Tooltip>
  );

};
