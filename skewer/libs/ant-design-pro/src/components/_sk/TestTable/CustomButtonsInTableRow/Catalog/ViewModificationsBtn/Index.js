import React from 'react';
import { Tooltip } from 'antd';
import _ from 'lodash'
import Icon from "../../../../Icon/Index";
import * as sk from '../../../../../../services/_sk/api'

const handleClick = (props) => e => {
  // останавливаем дальнейшее всплытие события
  e.stopPropagation();

  const {record, mainContainerData, dispatch} = props;

  if (!record.modifications) {
    return null;
  }

  dispatch({
    type: 'skGlobal/handleCustomButtonInParameters',
    payload: {
      dispatchCmd: 'modificationsItems',
      path: mainContainerData.get('path'),
      data: _.merge(
        {
          cmd: 'modificationsItems',
          data: record
        },
        mainContainerData.getIn(['params', 'serviceData']).toJS()
      )
    }
  });

};

export default (props) => {

  const {record, configButton} = props;

  const {tooltip} = configButton;

  if ( !record.modifications ){
    return null;
  }

  return (
    <Tooltip
      placement="bottomRight"
      title={tooltip}
    >
      <span>
        <Icon
          alias='icon-view'
          addProps={{onClick: handleClick(props)}}
        />
      </span>
    </Tooltip>
  );
};
