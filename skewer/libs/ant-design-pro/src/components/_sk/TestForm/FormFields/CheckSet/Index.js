import React from "react";
import CollapsiblePanels from '../../../CollapsiblePanels/Index'
import CheckField from '../CheckField/Index'

export default ({ configField, ...rest}) => {

  const { name: nameGroupField, title, value } = configField;
  const { groups } = value;

  let newGroup = [];

  const buildGroupsItems = (items, parentName) => {
    return items.map(({name, ...otherProps}) => {
      const config = {
        name: `${nameGroupField}_${parentName}_${name}`,
        ...otherProps
      };
      return <CheckField configField={config} {...rest} />
    })
  };

  groups.forEach(({title, name, items}) => {
    newGroup = [
      ...newGroup,
      {
        collapsible: true,
        collapsed: false,
        groupTitle: title,
        items: buildGroupsItems(items, name)
      }
    ]
  });

  return (
    <CollapsiblePanels
      groups={[{collapsible: true, collapsed: false, groupTitle: title, items: [<CollapsiblePanels groups={newGroup}/>]}]}
    />
  )
}
