import React from 'react';
import { styled } from '@mui/material/styles';
import {
  DataGrid,
  type GridColDef,
  type GridValidRowModel,
  type DataGridProps,
  Toolbar,
  ToolbarButton,
  ColumnsPanelTrigger,
  FilterPanelTrigger,
  ExportCsv,
  ExportPrint,
  QuickFilter,
  QuickFilterControl,
  QuickFilterClear,
  QuickFilterTrigger,
} from '@mui/x-data-grid';
import {
  Box,
  Tooltip,
  Menu,
  Badge,
  MenuItem,
  Divider,
  TextField,
  InputAdornment,
  Typography,
} from '@mui/material';
import ViewColumnIcon from '@mui/icons-material/ViewColumn';
import FilterListIcon from '@mui/icons-material/FilterList';
import FileDownloadIcon from '@mui/icons-material/FileDownload';
import CancelIcon from '@mui/icons-material/Cancel';
import SearchIcon from '@mui/icons-material/Search';
import * as XLSX from 'xlsx';

export type ExcelConfig = {
  rows?: any[];
  fileName?: string;
  sheetName?: string;
};

export type AppDataGridProps<R extends GridValidRowModel = any> = Omit<
  DataGridProps<R>,
  'slots' | 'slotProps'
> & {
  title?: string;
  excel?: ExcelConfig | false;
  dataGridSlotProps?: DataGridProps<R>['slotProps'];
};

type OwnerState = { expanded: boolean };

const StyledQuickFilter = styled(QuickFilter)({
  display: 'grid',
  alignItems: 'center',
});

const StyledToolbarButton = styled(ToolbarButton)<{ ownerState: OwnerState }>(({ theme, ownerState }) => ({
  gridArea: '1 / 1',
  width: 'min-content',
  height: 'min-content',
  zIndex: 1,
  opacity: ownerState.expanded ? 0 : 1,
  pointerEvents: ownerState.expanded ? 'none' : 'auto',
  transition: theme.transitions.create(['opacity']),
}));

const StyledTextField = styled(TextField)<{ ownerState: OwnerState }>(({ theme, ownerState }) => ({
  gridArea: '1 / 1',
  overflowX: 'clip',
  width: ownerState.expanded ? 260 : 'var(--trigger-width)',
  opacity: ownerState.expanded ? 1 : 0,
  transition: theme.transitions.create(['width', 'opacity']),
}));

function DefaultToolbar({ title, excel }: { title?: string; excel?: ExcelConfig | false }) {
  const [exportMenuOpen, setExportMenuOpen] = React.useState(false);
  const exportMenuTriggerRef = React.useRef<HTMLButtonElement>(null);

  const handleExportExcel = () => {
    if (!excel || !excel.rows || excel.rows.length === 0) return;
    const worksheet = XLSX.utils.json_to_sheet(excel.rows);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, excel.sheetName || 'Sheet1');
    XLSX.writeFile(workbook, excel.fileName || 'data.xlsx');
  };

  return (
    <Toolbar>
      {title ? (
        <Typography fontWeight="medium" sx={{ flex: 1, mx: 0.5 }}>
          {title}
        </Typography>
      ) : (
        <Box sx={{ flex: 1 }} />
      )}

      <Tooltip title="Columns">
        <ColumnsPanelTrigger render={<ToolbarButton />}>
          <ViewColumnIcon fontSize="small" />
        </ColumnsPanelTrigger>
      </Tooltip>

      <Tooltip title="Filters">
        <FilterPanelTrigger
          render={(props, state) => (
            <ToolbarButton {...props} color="default">
              <Badge badgeContent={state.filterCount} color="primary" variant="dot">
                <FilterListIcon fontSize="small" />
              </Badge>
            </ToolbarButton>
          )}
        />
      </Tooltip>

      <Divider orientation="vertical" variant="middle" flexItem sx={{ mx: 0.5 }} />

      <Tooltip title="Export">
        <ToolbarButton
          ref={exportMenuTriggerRef}
          id="export-menu-trigger"
          aria-controls="export-menu"
          aria-haspopup="true"
          aria-expanded={exportMenuOpen ? 'true' : undefined}
          onClick={() => setExportMenuOpen(true)}
        >
          <FileDownloadIcon fontSize="small" />
        </ToolbarButton>
      </Tooltip>

      <Menu
        id="export-menu"
        anchorEl={exportMenuTriggerRef.current}
        open={exportMenuOpen}
        onClose={() => setExportMenuOpen(false)}
        anchorOrigin={{ vertical: 'bottom', horizontal: 'right' }}
        transformOrigin={{ vertical: 'top', horizontal: 'right' }}
        slotProps={{
          list: {
            'aria-labelledby': 'export-menu-trigger',
          },
        }}
      >
        <ExportPrint render={<MenuItem />} onClick={() => setExportMenuOpen(false)}>
          Print
        </ExportPrint>
        <ExportCsv render={<MenuItem />} onClick={() => setExportMenuOpen(false)}>
          Download as CSV
        </ExportCsv>
        {excel !== false && excel?.rows && excel.rows.length > 0 && (
          <MenuItem onClick={() => { handleExportExcel(); setExportMenuOpen(false); }}>
            Download as Excel
          </MenuItem>
        )}
      </Menu>

      <StyledQuickFilter>
        <QuickFilterTrigger
          render={(triggerProps, state) => (
            <Tooltip title="Search" enterDelay={0}>
              <StyledToolbarButton
                {...triggerProps}
                ownerState={{ expanded: state.expanded }}
                color="default"
                aria-disabled={state.expanded}
              >
                <SearchIcon fontSize="small" />
              </StyledToolbarButton>
            </Tooltip>
          )}
        />
        <QuickFilterControl
          render={({ ref, ...controlProps }, state) => (
            <StyledTextField
              {...controlProps}
              ownerState={{ expanded: state.expanded }}
              inputRef={ref}
              aria-label="Search"
              placeholder="Search..."
              size="small"
              slotProps={{
                input: {
                  startAdornment: (
                    <InputAdornment position="start">
                      <SearchIcon fontSize="small" />
                    </InputAdornment>
                  ),
                  endAdornment: state.value ? (
                    <InputAdornment position="end">
                      <QuickFilterClear
                        edge="end"
                        size="small"
                        aria-label="Clear search"
                        material={{ sx: { marginRight: -0.75 } }}
                      >
                        <CancelIcon fontSize="small" />
                      </QuickFilterClear>
                    </InputAdornment>
                  ) : null,
                  ...controlProps.slotProps?.input,
                },
                ...controlProps.slotProps,
              }}
            />
          )}
        />
      </StyledQuickFilter>
    </Toolbar>
  );
}

export default function AppDataGrid<R extends GridValidRowModel = any>(props: AppDataGridProps<R>) {
  const { title, excel, dataGridSlotProps, sx, ...dgProps } = props as AppDataGridProps<R> & { sx?: DataGridProps<R>['sx'] };

  return (
    <Box sx={{ width: '100%' }}>
      <DataGrid
        autoHeight
        disableRowSelectionOnClick
        showToolbar
        {...dgProps}
        slots={{
          toolbar: () => <DefaultToolbar title={title} excel={excel} />,
          ...(dgProps as any).slots,
        }}
        slotProps={{
          ...dataGridSlotProps,
        }}
        sx={{ '& .MuiDataGrid-cell': { display: 'flex', alignItems: 'center' }, ...(sx as any) }}
      />
    </Box>
  );
}
