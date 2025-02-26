<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { 
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { ArrowLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref, computed } from 'vue';

// Define invoice item interface
interface InvoiceItem {
    description: string;
    quantity: number;
    price: number;
}

// Define invoice interface
interface Invoice {
    id: string;
    client_name: string;
    client_email: string;
    client_address: string;
    invoice_date: string;
    due_date: string;
    status: string;
    items: InvoiceItem[];
    notes?: string;
    terms: string;
}

// Define props for the invoice data
const props = defineProps<{
    invoice: Invoice
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Invoicing',
        href: '/dashboard',
    },
    {
        title: `Invoice #${props.invoice.id}`,
        href: `/invoices/${props.invoice.id}`,
    },
    {
        title: 'Edit',
        href: `/invoices/${props.invoice.id}/edit`,
    },
];

// Form for editing the invoice
const form = useForm({
    client_name: props.invoice.client_name,
    client_email: props.invoice.client_email,
    client_address: props.invoice.client_address,
    invoice_date: props.invoice.invoice_date,
    due_date: props.invoice.due_date,
    status: props.invoice.status,
    items: props.invoice.items.map(item => ({
        description: item.description,
        quantity: item.quantity,
        price: item.price
    })),
    notes: props.invoice.notes || '',
    terms: props.invoice.terms,
});

// Calculate subtotal
const subtotal = computed(() => {
    let total = 0;
    for (const item of form.items) {
        total += Number(item.quantity) * Number(item.price);
    }
    return total;
});

// Calculate tax (20% VAT)
const tax = computed(() => {
    return subtotal.value * 0.20;
});

// Calculate total
const total = computed(() => {
    return subtotal.value + tax.value;
});

// Add a new item to the invoice
const addItem = () => {
    form.items.push({ description: '', quantity: 1, price: 0 });
};

// Remove an item from the invoice
const removeItem = (index: number) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

// Submit the form
const submit = () => {
    form.put(`/invoices/${props.invoice.id}`, {
        onSuccess: () => {
            // Redirect to invoice view or show success message
            router.visit(`/invoices/${props.invoice.id}`);
        },
    });
};
</script>

<template>
    <Head :title="`Edit Invoice #${invoice.id}`" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto py-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Edit Invoice #{{ invoice.id }}</h1>
                <div class="flex space-x-2">
                    <Button variant="outline" @click="router.visit(`/invoices/${invoice.id}`)">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Cancel
                    </Button>
                </div>
            </div>
            
            <form @submit.prevent="submit">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Client Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Client Information</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div>
                                    <Label for="client_name">Client Name</Label>
                                    <Input id="client_name" v-model="form.client_name" required />
                                </div>
                                <div>
                                    <Label for="client_email">Client Email</Label>
                                    <Input id="client_email" type="email" v-model="form.client_email" required />
                                </div>
                                <div>
                                    <Label for="client_address">Client Address</Label>
                                    <textarea id="client_address" v-model="form.client_address" required class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    
                    <!-- Invoice Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Invoice Details</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-4">
                                <div>
                                    <Label for="invoice_date">Invoice Date</Label>
                                    <Input id="invoice_date" type="date" v-model="form.invoice_date" required />
                                </div>
                                <div>
                                    <Label for="due_date">Due Date</Label>
                                    <Input id="due_date" type="date" v-model="form.due_date" required />
                                </div>
                                <div>
                                    <Label for="status">Status</Label>
                                    <select id="status" v-model="form.status" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                        <option value="Paid">Paid</option>
                                        <option value="Pending">Pending</option>
                                        <option value="Overdue">Overdue</option>
                                    </select>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
                
                <!-- Invoice Items -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Invoice Items</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-12 gap-4 items-end">
                                <div class="col-span-6">
                                    <Label :for="`item_description_${index}`">Description</Label>
                                    <Input :id="`item_description_${index}`" v-model="item.description" required />
                                </div>
                                <div class="col-span-2">
                                    <Label :for="`item_quantity_${index}`">Quantity</Label>
                                    <Input :id="`item_quantity_${index}`" type="number" v-model="item.quantity" min="1" required />
                                </div>
                                <div class="col-span-3">
                                    <Label :for="`item_price_${index}`">Price (€)</Label>
                                    <Input :id="`item_price_${index}`" type="number" v-model="item.price" min="0" step="0.01" required />
                                </div>
                                <div class="col-span-1 flex justify-end">
                                    <Button 
                                        type="button" 
                                        variant="destructive" 
                                        size="icon" 
                                        @click="removeItem(index)"
                                        :disabled="form.items.length <= 1"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                            
                            <Button type="button" variant="outline" @click="addItem" class="w-full">
                                <Plus class="mr-2 h-4 w-4" />
                                Add Item
                            </Button>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-between">
                        <div>
                            <div class="text-sm text-gray-500">Subtotal</div>
                            <div class="font-medium">{{ subtotal.toFixed(2) }} €</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Tax (20%)</div>
                            <div class="font-medium">{{ tax.toFixed(2) }} €</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500">Total</div>
                            <div class="font-bold text-lg">{{ total.toFixed(2) }} €</div>
                        </div>
                    </CardFooter>
                </Card>
                
                <!-- Additional Information -->
                <Card class="mb-6">
                    <CardHeader>
                        <CardTitle>Additional Information</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <Label for="notes">Notes</Label>
                                <textarea id="notes" v-model="form.notes" placeholder="Any additional notes for the client..." class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                            </div>
                            <div>
                                <Label for="terms">Terms and Conditions</Label>
                                <textarea id="terms" v-model="form.terms" class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"></textarea>
                            </div>
                        </div>
                    </CardContent>
                </Card>
                
                <!-- Submit Button -->
                <div class="flex justify-end">
                    <Button type="submit" class="w-full md:w-auto">
                        <Save class="mr-2 h-4 w-4" />
                        Save Changes
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template> 